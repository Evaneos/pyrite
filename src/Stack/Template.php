<?php

namespace Pyrite\Stack;

use DICIT\Container;

use Pyrite\StackDispatched;

use Symfony\Component\HttpKernel\TerminableInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Stack\StackedHttpKernel;
use Pyrite\Kernel\Exception\HttpException;

class Template extends StackDispatched implements TerminableInterface
{
    private $services;
    
    private $container;
    
    private $stacks = array();
    
    public function __construct(array $services, Container $container)
    {
        $this->services;
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        $this->stacks = $this->buildStacks();
        
        return $this->stacks[0]->handle($request, $type, $catch);
    }
    
    /**
     * {@inheritDoc}
     */
    public function terminate(Request $request, Response $response) 
    {
        if (!empty($this->stacks)) {
            foreach ($this->stacks as $stack) {
                if ($stack instanceof TerminableInterface) {
                    $stack->terminate($request, $response);
                }
            }
        }
    }
    
    /**
     * Build stacks
     *
     * @return \SplStack
     */
    protected function buildStacks()
    {
        $stacks       = array();
        $stackWrapped = null;
        
        foreach ($this->services as $service) {
            $stackDispatched = $this->container->get($service);
            
            if (!$stackDispatched instanceof StackDispatched) {
                throw new \RuntimeException(sprintf("Object of class %s is not an instance of \Pyrite\StackDispatched", get_class($stackDispatched)), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            
            if (isset($this->parameters[$service])) {
                $stackDispatched->setParameters($this->parameters[$service]);
            }
            
            $stacks[]      = $stackDispatched;
            $stackWrapped = $stackDispatched;
        }
    
        if (count($stacks) == 0) {
            throw new HttpException(Response::HTTP_NOT_IMPLEMENTED);
        }
        
        foreach ($stacks as $index => $stack) {
            if (isset($stacks[$index + 1])) {
                $stack->setStackWrapped($stacks[$index + 1]);
            } elseif (null !== $this->stackWrapped) {
                $stack->setStackWrapped($this->stackWrapped);
            }
        }
        
        return $stacks;
    }
}