<?php

namespace Pyrite\Routing;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;

class Director
{
    /**
     * @var Request
     */
    protected $request = null;
    /**
     * @var RequestContext
     */
    protected $requestContext = null;
    /**
     * @var string
     */
    protected $path = '';

    /**
     * @param Request $request
     * @param string  $path
     */
    public function __construct(Request $request, $path = '')
    {
        $context = new RequestContext();
        $context->fromRequest($request);
        $this->request = $request;
        $this->requestContext = $context;
        $this->path = $path;
    }

    /**
     * @param  RouteConfigurationBuilder $builder
     *
     * @return RouteConfiguration
     */
    public function build(RouteConfigurationBuilder $builder)
    {
        $builder->setRequest($this->request);
        $builder->setRequestContext($this->requestContext);
        $builder->setConfigurationPath($this->path);
        return $builder->build();
    }
}