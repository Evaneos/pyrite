<?php

namespace Pyrite\Factory;

use Pyrite\Container\Container;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class StackedHttpKernel implements HttpKernelFactory
{
    /**
     * Array of service name to use
     *
     * @var string[]
     */
    private $services;

    /**
     * DIC to use when getting services
     *
     * @var Container
     */
    private $container;

    /**
     * StackedHttpKernel constructor.
     *
     * @param Container $container
     * @param           $services
     */
    public function __construct(Container $container, $services)
    {
        $this->services  = $services;
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function register(HttpKernelInterface $app = null, $name = '', array $parameters = array())
    {
        $lastServiceName = call_user_func('end', array_keys($this->services));
        $builder         = new \Stack\Builder();
        $container       = $this->container;

        foreach ($this->services as $serviceName => $serviceParameters) {

            $factory       = $this->getFactory($serviceName);
            $configuration = $this->getConfiguration($serviceName, $parameters);

            $builder->push(function ($app) use ($container, $factory, $serviceName, $configuration) {
                list($stackName, $stack) = $factory->register($app, $serviceName, $configuration);
                return $stack;
            });
        }

        $factory                 = $this->getFactory($lastServiceName);
        $configuration           = $this->getConfiguration($lastServiceName, $parameters);
        list($stackName, $stack) = $factory->register($app, $lastServiceName, $configuration);

        $stackResolved = $builder->resolve($stack);
        return array($name, $stackResolved);
    }

    /**
     * Get a factory given a service name
     *
     * @param  string            $factoryName Service name to get factory from
     * @throws \RuntimeException Throws a exception if service does not implment HttpKernelFactory
     *
     * @return \Pyrite\Factory\HttpKernelFactory a factory for a service
     */
    private function getFactory($factoryName)
    {
        $factory = $this->container->get($factoryName);

        if (!$factory instanceof HttpKernelFactory) {
            throw new \RuntimeException(sprintf("Object of class %s does not implement Pyrite\Factory\HttpKernelFactory interface", get_class($factory)));
        }

        return $factory;
    }

    /**
     * Get configuration for a service
     *
     * @param string $serviceName
     * @param array  $parameters
     *
     * @return array Configuration for a Service
     */
    private function getConfiguration($serviceName, $parameters)
    {
        return isset($parameters[$serviceName]) ? $parameters[$serviceName] : array();
    }
}
