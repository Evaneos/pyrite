<?php

namespace Pyrite\Kernel;

use DICIT\Activator;
use DICIT\ActivatorFactory;
use DICIT\Config\AbstractConfig;
use DICIT\Config\ArrayConfig;
use DICIT\Config\PHP;
use DICIT\Config\YML;
use EVFramework\Container\DICITAdapter;
use Monolog\ErrorHandler;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use Pyrite\Config\NullConfig;
use Pyrite\Container\Container;
use Pyrite\Errors\ErrorSubscriber;
use Pyrite\Errors\ErrorSubscription;
use Pyrite\Exception\BadConfigurationException;
use Pyrite\Factory\StackedHttpKernel;
use Pyrite\Logger\LoggerFactory;
use Pyrite\Routing\Router;
use Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;

/**
 * PyriteKernel
 *
 * Main kernel for a pyrite application
 */
class PyriteKernel implements HttpKernelInterface, TerminableInterface
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var \Stack\StackedHttpKernel
     */
    private $stack;

    /**
     * @var bool
     */
    private $booted;

    /**
     * @var string
     */
    private $containerConfigPath;

    /**
     * @var ParameterBag
     */
    private $config;

    /**
     * @var bool
     */
    private $started;

    /**
     * @var array
     */
    private $internalConfig;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @var ErrorSubscriber
     */
    private $errorSubscriber;

    /**
     * @var string[]
     */
    private $errorLevelMap = array(
        E_ERROR             => LogLevel::CRITICAL,
        E_WARNING           => LogLevel::WARNING,
        E_PARSE             => LogLevel::ALERT,
        E_NOTICE            => LogLevel::WARNING,
        E_CORE_ERROR        => LogLevel::CRITICAL,
        E_CORE_WARNING      => LogLevel::WARNING,
        E_COMPILE_ERROR     => LogLevel::ALERT,
        E_COMPILE_WARNING   => LogLevel::WARNING,
        E_USER_ERROR        => LogLevel::ERROR,
        E_USER_WARNING      => LogLevel::WARNING,
        E_USER_NOTICE       => LogLevel::NOTICE,
        E_STRICT            => LogLevel::WARNING,
        E_RECOVERABLE_ERROR => LogLevel::ERROR,
        E_DEPRECATED        => LogLevel::NOTICE,
        E_USER_DEPRECATED   => LogLevel::NOTICE,
    );


    /**
     * PyriteKernel constructor.
     *
     * @param string $containerConfigPath
     * @param Router $router
     */
    public function __construct($containerConfigPath, Router $router, ErrorSubscriber $errorSubscriber = null)
    {
        mb_internal_encoding('UTF-8');
        $this->logger = new NullLogger();
        $this->booted = false;
        $this->containerConfigPath = $containerConfigPath;
        $this->started = false;
        $this->internalConfig = array();
        $this->router = $router;
        $this->errorSubscriber = null === $errorSubscriber ? new ErrorSubscriber() : $errorSubscriber;
        $this->boot();
    }

    /**
     * @param Request    $request
     * @param \Exception $e
     */
    private function handleException(Request $request, \Exception $e)
    {
        $subscriptions = $this->errorSubscriber->getSubscribedError();
        $collection = $this->router->getRouteCollection();

        /**
         * @var string $class
         * @var ErrorSubscription $subscription
         */
        foreach($subscriptions as $class => $subscription){
            if($e instanceof $class){
                $code = $subscription->getHttpCode();
                $routeName = $subscription->getRouteName();

                $request->attributes->set('_route', $routeName);
                $route = $collection->get($routeName.'.'.$request->attributes->get('locale'));
                $request->attributes->set('dispatch', $route->getOption('dispatch'));
                $request->attributes->set('http-status-code', $code);

                break;
            }
        }

        $factory = new StackedHttpKernel($this->container, $dispatch = $request->attributes->get('dispatch'));
        list($name, $this->stack) = $factory->register($this, 'pyrite.root_kernel', $dispatch);
    }

    /**
     * {@inheritDoc}
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        $this->container->bind('Request', $request);

        if(false === $this->config->get('debug')){
            $this->registerFatalErrorHandler();
        }

        $factory = new StackedHttpKernel($this->container, $dispatch = $request->attributes->get('dispatch'));
        list($name, $this->stack) = $factory->register($this, 'pyrite.root_kernel', $dispatch);

        try{
            return $this->stack->handle($request, $type, $catch);
        } catch(\Exception $e) {
            $this->handleException($request, $e);
        }

        return $this->stack->handle($request, $type, $catch);
    }

    /**
     * @return LoggerFactory
     */
    public function getLoggerFactory()
    {
        return $this->loggerFactory;
    }

    /**
     * @param Request  $request
     * @param Response $response
     */
    public function terminate(Request $request, Response $response)
    {
        $this->stack->terminate($request, $response);
    }

    public function boot()
    {
        if(true === $this->booted){
            return;
        }

        $this->internalConfig = $this->getContainerConfiguration($this->containerConfigPath)->load();
        $this->config = new ParameterBag($this->internalConfig['parameters']);
        $this->config->set('debug', !$this->config->get('production_mode', true));

        if(null === $this->config->get('root_dir')){
            throw new BadConfigurationException('You should define root_dir in config');
        }

        $this->config->set('log_dir', $this->config->get('root_dir').'../log');

        $this->loggerFactory = new LoggerFactory($this->getConfig()->get('debug'), $this->config->get('log_dir'));
        $appLogger = $this->loggerFactory->create('app');

        if(false === $this->config->get('debug')){
            error_reporting(E_ALL & ~E_USER_DEPRECATED & ~E_DEPRECATED);
            ini_set('display_errors', 'Off');
        }else{
            error_reporting(E_ALL);
            ini_set('display_errors', 'On');
        }

        ErrorHandler::register($appLogger, $this->errorLevelMap);

        $this->booted = true;
    }

    /**
     * @return DICITAdapter|Container
     */
    public function startContainer()
    {
        if($this->isStarted()){
            return;
        }

        $configAsArray = iterator_to_array($this->config->getIterator());

        $this->internalConfig['parameters'] = $configAsArray;
        $this->container = $this->createContainer(new ArrayConfig($this->internalConfig));

        // Ensure you can't touch it !
        unset($this->config); //remove references across shared services
        $this->config = new FrozenParameterBag($configAsArray);

        $currentLocale = $this->config->get('current_locale');
        $cookieParameters = $this->config->get('cookie_parameters');
        $domain = $cookieParameters[$currentLocale]['domain'];

        $loggerFactory = new LoggerFactory(
            $currentLocale,
            $domain,
            $this->config->get('debug'),
            $this->config->get('log_dir')
        );

        $this->container->bind('LoggerFactory', $loggerFactory);

        // Keep compatibility, use the right type hint in the application as usual (UrlGeneratorInterface, UrlMatcherInterface)
        $this->container->bind('UrlMatcher', $this->router->getUrlMatcher());
        $this->container->bind('UrlGenerator', $this->router->getUrlGenerator());
        $this->container->bind('RouteCollection', $this->router->getRouteCollection());
        $this->container->bind('AppLogger', $this->loggerFactory->getLogger('app'));

        $this->started = true;

        return $this->container;
    }

    private function registerFatalErrorHandler()
    {
        $errorLevel = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR);

        $fatalErrorHandler = function($code, $description, $file, $line) use ($errorLevel) {
            if (0 === error_reporting() || !in_array($code, $errorLevel)) {
                return;
            }

            $this->handleException(
                $request = $this->container->get('Request'),
                new \ErrorException($description, $code, 1, $file, $line)
            );

            $response = $this->stack->handle($request, HttpKernelInterface::MASTER_REQUEST, true);
            $response->send();
        };

        register_shutdown_function(function() use ($fatalErrorHandler) {
            $error = error_get_last();

            $fatalErrorHandler(
                $error['type'],
                $error['message'],
                $error['file'],
                $error['line']
            );
        });
    }

    /**
     * @return bool
     */
    public function isStarted()
    {
        return true === $this->started;
    }

    /**
     * @return ParameterBag
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param string $containerPath
     *
     * @return PHP|YML|NullConfig
     */
    private function getContainerConfiguration($containerPath)
    {
        $config = new NullConfig();

        if (null !== $containerPath && false !== strpos($containerPath, '.yml')) {
            $config = new YML($containerPath);
        }

        if (null !== $containerPath && false !== strpos($containerPath, '.php')) {
            $config = new PHP($containerPath);
        }

        return $config;
    }

    /**
     * @param AbstractConfig $config
     *
     * @return DICITAdapter
     */
    private function createContainer(AbstractConfig $config)
    {
        $activator = new ActivatorFactory();
        $container = new DICITAdapter($config, $activator);

        /** @var Activator $securityActivator */
        $securityActivator = $container->get('SecurityActivator');

        $activator->addActivator('security', $securityActivator, false);

        return $container;
    }

    /**
     * Until the kernel is not booted NOR started container is null
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }
}
