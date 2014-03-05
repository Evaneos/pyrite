<?php

namespace Pyrite\Routing;

use Pyrite\Config\NullConfig;
use DICIT\Config\YML;
use DICIT\Config\PHP;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

class RouteCollectionBuilder
{
    public static function buildFromFile($routePath)
    {
        $config = new NullConfig();

        if (null !== $routePath && preg_match('/.*yml$/', $routePath)) {
            $config = new YML($routePath);
        }

        if (null !== $routePath && preg_match('/.*php$/', $routePath)) {
            $config = new PHP($routePath);
        }


        return static::buildFromObject($config);
    }


    public static function buildFromObject(\DICIT\Config\AbstractConfig $config)
    {
        $configuration = $config->load();

        $routes = new RouteCollection();

        foreach ($configuration['routes'] as $name => $routeParameters) {
            $route = new Route($routeParameters['route']['pattern'], array(), array(), $routeParameters, '', array(), $routeParameters['route']['methods']);
            $routes->add($name, $route);
        }

        return $routes;
    }
}