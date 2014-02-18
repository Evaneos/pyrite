<?php

namespace Pyrite\Stack;

use DICIT\Container;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\TerminableInterface;

class Controller implements HttpKernelInterface, TerminableInterface
{
    /**
     * Controller used
     * 
     * @var object
     */
    private $controller;
    
    /**
     * Method used
     * 
     * @var string
     */
    private $method;
    
    /**
     * Controller used
     * 
     * @var HttpKernelInterface
     */
    private $app;
    
    public function __construct($controller, $method, HttpKernelInterface $app = null)
    {
        $this->app        = $app;
        $this->controller = $controller;
        $this->method     = $method;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        $method   = $this->method;
        $response = $this->controller->$method($request);
        
        if ($response instanceof Response) {
            return $response;
        }
        
        $request->attributes->set('_controller_data', $response);
        
        if (null !== $this->app) {
            return $this->app->handle($request, $type, $catch);
        }
        
        throw new \RuntimeException(sprintf("No response where returned from controller %s->%s and no kernel left to play, something is missing.", get_class($this->controller), $this->method));
    }

    /**
     * {@inheritDoc}
     */
    public function terminate(Request $request, Response $response)
    {
        if (null !== $this->controller && $this->controller instanceof TerminableInterface) {
            $this->controller->terminate($request, $response);
        }
    }
}
