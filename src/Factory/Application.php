<?php

namespace Pyrite\Factory;

use DICIT\Container;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Application implements HttpKernelFactory
{
    private $container;

    protected $callbacks = array();

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function addCatchable($name, \Pyrite\Exception\Callback $callback) {
        $this->callbacks[$name] = $callback;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function register(HttpKernelInterface $app = null, $name = '', array $parameters = array())
    {
        //Register
        $service = new \Pyrite\Stack\Application($this->container, $app, $parameters);
        $service->registerCatchable($type, $callback);

        $name = uniqid();
        return array($name, $service);
    }
}