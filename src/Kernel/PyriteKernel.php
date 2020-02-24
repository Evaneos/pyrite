<?php

namespace Pyrite\Kernel;

use DICIT\ActivatorFactory;
use DICIT\Config\AbstractConfig;
use DICIT\Config\ArrayConfig;
use DICIT\Config\PHP;
use DICIT\Config\YML;
use DICIT\Container;
use EVFramework\Container\DICITAdapter;
use Monolog\ErrorHandler;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use Pyrite\Config\NullConfig;
use Pyrite\Container\Container as ContainerInterface;
use Pyrite\Errors\ErrorSubscriber;
use Pyrite\Errors\ErrorSubscription;
use Pyrite\Exception\BadConfigurationException;
use Pyrite\Factory\StackedHttpKernel;
use Pyrite\Logger\LoggerFactory;
use Pyrite\Routing\Router;
use Stack\Builder;
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
     * @var ContainerInterface
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

    /** @var Builder */
    private $builder;

    /**
     * @var bool
     */
    private $isResolved;

    /** @var HttpKernelInterface */
    private $resolvedApp;

    /** @var string[] */
    private $activators;

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
     * @param                      $containerConfigPath
     * @param Router|null               $router
     * @param ErrorSubscriber|null $errorSubscriber
     */
    public function __construct($containerConfigPath, Router $router = null, ErrorSubscriber $errorSubscriber = null)
    {
        mb_internal_encoding('UTF-8');
        $this->builder = new Builder();
        $this->logger = new NullLogger();
        $this->booted = false;
        $this->containerConfigPath = $containerConfigPath;
        $this->started = false;
        $this->internalConfig = array();
        $this->activators = array();
        $this->router = $router;
        $this->isResolved = false;
        $this->errorSubscriber = null === $errorSubscriber ? new ErrorSubscriber() : $errorSubscriber;
        $this->boot();
    }

    /**
     * @param string $name
     * @param string $serviceName
     */
    public function addActivator($name, $serviceName)
    {
        $this->activators[$name] =  $serviceName;
    }

    /**
     * @return $this
     */
    public function push()
    {
        if (func_num_args() === 0) {
            throw new \InvalidArgumentException("Missing argument(s) when calling push");
        }

        call_user_func_array(array($this->builder, 'push'), func_get_args());

        return $this;
    }

    /**
     * @param Request    $request
     * @param \Exception $e
     */
    public function handleException(Request $request, \Exception $e)
    {
        if(true === $this->config->get('debug')){
            throw $e; // Will be handle via the debug stack
        }

        $subscriptions = $this->errorSubscriber->getSubscribedError();

        if(null === $this->router){ // CLI
            throw $e;
        }

        $collection = $this->router->getRouteCollection();

        /**
         * @var string $class
         * @var ErrorSubscription $subscription
         */
        foreach($subscriptions as $class => $subscription){
            if($e instanceof $class){
                $code = $subscription->getHttpCode();
                $routeName = $subscription->getRouteName();

                if($code >= 500){
                    $this->loggerFactory->getLogger('app')->emergency($e);
                }

                $request->attributes->set('_route', $routeName);
                $route = $collection->get($routeName.'.'.$request->attributes->get('locale'));

                if(null !== $route){
                    $request->attributes->set('dispatch', $route->getOption('dispatch'));
                    $request->attributes->set('http-status-code', $code);
                    $request->attributes->set('exception', $e);
                }else{
                    throw $e;
                }

                break;
            }
        }

        $this->run($request, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * @param Request $request
     */
    public function run(Request $request, $type = HttpKernelInterface::MASTER_REQUEST)
    {
        if($type === self::SUB_REQUEST){
            $this->doRun($request, $type);
            return;
        }

        try{
            $this->doRun($request, $type);
            return;
        } catch(\Exception $e) {
            $this->handleException($request, $e);
        }
    }

    /**
     * @param Request $request
     * @param         $type
     */
    private function doRun(Request $request, $type)
    {
        if(false === $this->isResolved){
            $this->resolvedApp = $this->builder->resolve($this);
            $this->isResolved = true;
        }

        $response = $this->resolvedApp->handle($request, $type);
        $response->send();

        if ($this->resolvedApp instanceof TerminableInterface) {
            $this->resolvedApp->terminate($request, $response);
        }
    }

    /**
     * @param Request $request
     * @param int     $type
     * @param bool    $catch
     *
     * @return Response
     * @throws \Exception
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        if(false === $this->config->get('debug')){
            $this->registerFatalErrorHandler();
        }

        $factory = new StackedHttpKernel($this->container, $dispatch = $request->attributes->get('dispatch'));

        list($name, $this->stack) = $factory->register($this, 'pyrite.root_kernel', $dispatch);

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
        if($this->stack instanceof TerminableInterface){
            $this->stack->terminate($request, $response);
        }
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

        $logDir = $this->config->get('log_dir');

        if(null === $logDir){
            $this->config->set('log_dir', $this->config->get('root_dir').'../log');
        }

        $this->loggerFactory = new LoggerFactory($this->getConfig()->get('debug'), $this->config->get('log_dir'));
        $appLogger = $this->loggerFactory->create('app');

        if(false === $this->config->get('debug')){
            error_reporting(E_ALL & ~E_USER_DEPRECATED & ~E_DEPRECATED & ~E_NOTICE);
            ini_set('display_errors', 'Off');
        }else{
            error_reporting(E_ALL);
            ini_set('display_errors', 'On');
        }

        ErrorHandler::register($appLogger, $this->errorLevelMap);

        $this->booted = true;
    }

    /**
     * @return ContainerInterface
     */
    public function startContainer()
    {
        if(true === $this->isStarted()){
            return $this->container;
        }

        $configAsArray = iterator_to_array($this->config->getIterator());

        $this->internalConfig['parameters'] = $configAsArray;
        $this->container = $this->createContainer(new ArrayConfig($this->internalConfig));

        $loggerFactory = new LoggerFactory(
            $this->config->get('debug'),
            $this->config->get('log_dir')
        );

        $this->container->bind('LoggerFactory', $loggerFactory);

        if(null !== $this->router){
            // Keep compatibility, use the right type hint in the application as usual (UrlGeneratorInterface, UrlMatcherInterface)
            $this->container->bind('UrlMatcher', $this->router->getUrlMatcher());
            $this->container->bind('UrlGenerator', $this->router->getUrlGenerator());
            $this->container->bind('RouteCollection', $this->router->getRouteCollection());
        }

        $this->container->bind('AppLogger', $this->loggerFactory->getLogger('app'));

        $this->started = true;

        return $this->container;
    }

    private function registerFatalErrorHandler()
    {
        $errorLevel = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR);

        $that = $this;

        $fatalErrorHandler = function($code, $description, $file, $line) use ($errorLevel, $that) {
            if (0 === error_reporting() || !in_array($code, $errorLevel)) {
                return;
            }

            $that->handleException(
                $request = $that->getContainer()->get('Request'),
                new \ErrorException($description, $code, 1, $file, $line)
            );
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
     * @param array          $activators ['activator_name' => 'service_name']
     *
     * @return DICITAdapter
     */
    private function createContainer(AbstractConfig $config)
    {
        $factory = new ActivatorFactory();

        $container = new DICITAdapter($config, $factory);

        foreach($this->activators as $name => $serviceName){
            $factory->addActivator($name, $container->get($serviceName), false);
        }

        if(true === $container->has('SecurityActivator')){
            $factory->addActivator('security', $container->get('SecurityActivator'), false);
        }

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
