<?php

namespace Pyrite\Factory;

use Symfony\Component\HttpKernel\HttpKernelInterface;

class Session implements HttpKernelFactory
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function register(HttpKernelInterface $app = null, $name = '', array $parameters = array())
    {
        if (null === $app) {
            throw new \RuntimeException("Session must have a wrapped kernel");
        }

        return array($name, new \Stack\Session($app, $parameters));
    }
}