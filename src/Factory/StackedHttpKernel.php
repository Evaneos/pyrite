<?php

namespace Pyrite\Factory;

use DICIT\Container;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\EventDispatcher\Tests\Service;

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
     * Base name to use when creating services (for Container)
     *
     * @var string
     */
    private $name;
    
    public function __construct(Container $container, $services, $name)
    {
        $this->services  = $services;
        $this->container = $container;
        $this->name      = $name;
    }
    
    /**
     * {@inheritDoc}
     */
    public function register(HttpKernelInterface $app = null, $routeName = '', $parameters = array())
    {
        $lastServiceName = array_pop($this->services);
        $builder         = new \Stack\Builder();
        $container       = $this->container;
        
        foreach ($this->services as $serviceName) {
            $factory       = $this->getFactory($serviceName);
            $configuration = $this->getConfiguration($serviceName, $parameters);
        
            $builder->push(function ($app) use($container, $factory, $routeName, $configuration) {
                list($name, $stack) = $factory->register($app, $routeName, $configuration);
        
                //Register stack in container @TODO
                //$container->bind($name, $stack);
                
                return $stack;
            });
        }
        
        $factory            = $this->getFactory($lastServiceName);
        $configuration      = $this->getConfiguration($lastServiceName, $parameters);
        list($name, $stack) = $factory->register($app, $routeName, $configuration);

        //Register stack in container @TODO
        //$container->bind($name, $stack);
        
        return array($this->name.".".uniqid(), $builder->resolve($stack));
    }
    
    /**
     * Get a factory given a service name
     * 
     * @param string $serviceName Service name to get factory from
     * @throws \RuntimeException Throws a exception if service does not implment HttpKernelFactory
     * 
     * @return \Pyrite\Factory\HttpKernelFactory a factory for a service
     */
    private function getFactory($serviceName)
    {
        $factory = $this->container->get($serviceName);
        
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