<?php

namespace Pyrite\Factory;

use DICIT\Container;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Application implements HttpKernelFactory
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function register(HttpKernelInterface $app = null, $name = '', array $parameters = array())
    {
        //Register
        $service = new \Pyrite\Stack\Application($this->container, $app, $parameters);
        $name = uniqid();

        return array($name, $service);
    }
}