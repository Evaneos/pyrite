<?php

namespace Pyrite\Routing;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;

interface RouteConfigurationBuilder
{
    /**
     * @param Request $request
     */
    public function setRequest(Request $request);

    /**
     * @param RequestContext $requestContext
     */
    public function setRequestContext(RequestContext $requestContext);

    /**
     * @return RouteConfiguration
     */
    public function build();
}
