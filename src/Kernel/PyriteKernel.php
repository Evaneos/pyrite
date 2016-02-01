<?php

namespace Pyrite\Kernel;

use DICIT\Activator;
use DICIT\ActivatorFactory;
use DICIT\Config\AbstractConfig;
use DICIT\Config\ArrayConfig;
use DICIT\Config\PHP;
use DICIT\Config\YML;
use EVFramework\Container\DICITAdapter;
use Psr\Log\NullLogger;
use Pyrite\Config\NullConfig;
use Pyrite\Container\Container;
use Pyrite\Factory\StackedHttpKernel;
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
     * PyriteKernel constructor.
     *
     * @param $containerConfigPath
     */
    public function __construct($containerConfigPath)
    {
        mb_internal_encoding('UTF-8');
        $this->logger = new NullLogger();
        $this->booted = false;
        $this->containerConfigPath = $containerConfigPath;
        $this->started = false;
        $this->internalConfig = array();

        $this->boot();
    }

    /**
     * {@inheritDoc}
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        $factory = new StackedHttpKernel($this->container, $dispatch = $request->attributes->get('dispatch'));

        list($name, $this->stack) = $factory->register($this, 'pyrite.root_kernel', $dispatch);

        return $this->stack->handle($request, $type, $catch);
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

        $this->started = true;

        return $this->container;
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
