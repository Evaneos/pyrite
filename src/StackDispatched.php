<?php

namespace Pyrite;

use Symfony\Component\HttpKernel\HttpKernelInterface;

abstract class StackDispatched implements HttpKernelInterface
{
    /**
     * Stack wrapped
     * 
     * @var StackDispatched
     */
    protected $stackWrapped;
    
    /**
     * Parameters
     * 
     * @var array
     */
    protected $parameters;
    
    /**
     * Set stack wrapped
     * 
     * @param StackDispatched $stackWrapped
     */
    public function setStackWrapped(StackDispatched $stackWrapped = null)
    {
        $this->stackWrapped = $stackWrapped;
    }
    
    /**
     * Set parameters for this stack
     * 
     * @param array $parameters
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }
}