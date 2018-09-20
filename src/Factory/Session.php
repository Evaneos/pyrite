<?php

namespace Pyrite\Factory;

use Pyrite\Container\Container;
use Pyrite\Stack\Session as StackSession;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Session implements HttpKernelFactory
{
    /** @var Container */
    private $container;

    /**
     * Session constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @inheritdoc
     *
     * @throws \RuntimeException
     */
    public function register(HttpKernelInterface $app = null, $name = '', array $parameters = [])
    {
        if (null === $app) {
            throw new \RuntimeException('Session must have a wrapped kernel');
        }

        $cookieParams = $this->container->getParameter('cookie');

        return [$name, new StackSession($app, $this->shouldStart($parameters), $cookieParams)];
    }

    /**
     * @param array $parameters
     *
     * @return bool
     */
    private function shouldStart(array $parameters)
    {
        $start = false;
        if (array_key_exists('start', $parameters) && is_scalar($parameters['start'])) {
            $start = (bool) $parameters['start'];
        }

        return $start;
    }
}
