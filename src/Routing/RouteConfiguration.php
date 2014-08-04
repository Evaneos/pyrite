<?php

namespace Pyrite\Routing;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RouteConfiguration
{
    /**
     * @return RouteCollection routeCollection
     */
    protected $routeCollection;

    /**
     * @return UrlGenerator urlGenerator
     */
    protected $urlGenerator;

    /**
     * @return RouteCollection routeCollection
     */
    public function getRouteCollection() {
        return $this->routeCollection;
    }
    /**
     * @param RouteCollection $value
     * @return RouteConfiguration
     */
    public function setRouteCollection($value) {
        $this->routeCollection = $value;
        return $this;
    }
    /**
     * @return UrlGenerator urlGenerator
     */
    public function getUrlGenerator() {
        return $this->urlGenerator;
    }
    /**
     * @param UrlGenerator $value
     * @return RouteConfiguration
     */
    public function setUrlGenerator($value) {
        $this->urlGenerator = $value;
        return $this;
    }

    /**
     * @param RouteCollection       $collection
     * @param UrlGeneratorInterface $generator
     */
    public function __construct(RouteCollection $collection, UrlGeneratorInterface $generator)
    {
        $this->routeCollection = $collection;
        $this->urlGenerator = $generator;
    }
}