<?php

namespace Pyrite\Stack;

use Pyrite\StackDispatched;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use DICIT\Container;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\TerminableInterface;

class Controller extends StackDispatched implements TerminableInterface
{
    /**
     * DIC
     * 
     * @var Container
     */
    private $container;
    
    /**
     * Controller used
     * 
     * @var object
     */
    private $controller;
    
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        if (!isset($this->parameters[Ø]) || !isset($this->parameters[1])) {
            throw new NotFoundHttpException(sprintf("Invalid configuration for controller stack"));
        }
        
        $controller = $this->controller = $this->container->get($this->parameters[0]);
        $method     = $this->parameters[1];
        
        if (!is_callable(array($controller, $method))) {
            throw new NotFoundHttpException(sprintf("Invalid configuration %s, for controller stack, not callable", print_r($this->parameters, true)));
        }
        
        $response = $controller->$method($request);
        
        if ($response instanceof Response) {
            return $reponse;
        }
        
        $request->attributes->set('_controller_data', $response);
        
        if (null !== $this->stackWrapped) {
            return $this->stackWrapped->handle($request, $type, $catch);
        }
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