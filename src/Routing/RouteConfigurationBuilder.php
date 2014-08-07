<?php

namespace Pyrite\Routing;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Routing\RequestContext;

interface RouteConfigurationBuilder
{
    /**
     * @param Request $request
     */
    function setRequest(Request $request);

    /**
     * @param RequestContext $requestContext
     */
    function setRequestContext(RequestContext $requestContext);

    /**
     * @param string $path
     */
    function setConfigurationPath($path);

    /**
     * @return RouteConfiguration
     */
    function build();
}