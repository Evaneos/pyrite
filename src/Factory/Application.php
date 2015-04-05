<?php

namespace Pyrite\Factory;

use Pyrite\Container\Container;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Application implements HttpKernelFactory
{
    private $container;

    protected $exceptionHandlers = array();

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function addExceptionHandler($name, \Pyrite\Exception\ExceptionHandler $handler)
    {
        $this->exceptionHandlers[$name] = $handler;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function register(HttpKernelInterface $app = null, $name = '', array $parameters = array())
    {
        //Register
        $service = new \Pyrite\Stack\Application($this->container, $app, $parameters, $this->exceptionHandlers);
        $name = uniqid();
        return array($name, $service);
    }
}
