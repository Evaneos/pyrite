<?php

namespace Pyrite\Factory;

use DICIT\Container;

use Pyrite\Stack\Controller as ControllerStack;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Controller implements HttpKernelFactory
{
    private $container;
    
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function register(HttpKernelInterface $app = null, $routeName = '', $parameters = array())
    {
        if (!isset($parameters[0]) || !isset($parameters[1])) {
            throw new NotFoundHttpException("Controller cannot be resolved.");
        }
        
        $controller = $this->container->get($parameters[0]);
        $method     = $parameters[1];
        
        if (!is_callable(array($controller, $method))) {
            throw new NotFoundHttpException(sprintf("Controller defined with this parameters %s is not callable.", print_r($this->parameters, true)));
        }
        
        //Register
        $service = new ControllerStack($app, $controller, $method);
        
        return array($name, $service);
    }
}