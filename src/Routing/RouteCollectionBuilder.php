<?php

namespace Pyrite\Routing;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Config\Resource\ResourceInterface;

class RouteCollectionBuilder
{
    /**
     * @param array $routingConfiguration
     *
     * @return RouteCollection
     */
    public static function build(array $routingConfiguration = array())
    {
        $routes = new RouteCollection();

        foreach ($routingConfiguration as $name => $routeParameters) {
            if (isset($routeParameters['route']['pattern']) && !is_string($routeParameters['route']['pattern'])) {
                throw new \RuntimeException(sprintf("Cannot build RouteCollection, non-string pattern for route %s", $name));
            }

            $route = new Route(
                    $routeParameters['route']['pattern'],
                    array(),
                    array_key_exists('regexp', $routeParameters['route']) ? $routeParameters['route']['regexp'] : array(),
                    $routeParameters,
                    '',
                    array(),
                    $routeParameters['route']['methods'],
                    null
            );
            $routes->add($name, $route);
        }
        return $routes;
    }
}
