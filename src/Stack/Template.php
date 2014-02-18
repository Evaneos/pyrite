<?php

namespace Pyrite\Stack;

use DICIT\Container;

use Pyrite\StackDispatched;

use Symfony\Component\HttpKernel\TerminableInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Yaml\Exception\RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Template is a stack plugin used to encapsulated other plugins
 * 
 * He is useful when you don't want to repeat the same list over and over in your routing
 * 
 * Parameters for this plugin is an hash where tke key correspond to the service name
 * and the value is the specific parameters for a service
 * 
 * If a wrapped stack is defined, the last element of this list will use it
 */
class Template extends StackDispatched implements TerminableInterface
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
     * A list of StackDisptached where the first is the one to run, 
     * this list is mostly useful for terminating the request 
     * otherwise only first stack element is required
     * 
     * @var StackDispatched[]
     */
    private $stacks = array();
    
    public function __construct(array $services, Container $container)
    {
        $this->services  = $services;
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
     * For each stack define in service we fetch the service, setting his parameters 
     * and define the next to process stack to the wrapped stack
     *
     * @throws RuntimeException Throw this exception if an element of the list does not implement StackDispatched
     *
     * @return array A list of StackDisptached where the first is the one to run, 
     *               this list is mostly useful for terminating the request 
     *               otherwise only first stack element is required
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