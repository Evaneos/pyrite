<?php

namespace Pyrite\Routing;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RouteConfiguration
{
    /**
     * @var RouteCollection routeCollection
     */
    protected $routeCollection;

    /**
     * @var UrlGeneratorInterface urlGenerator
     */
    protected $urlGenerator;

    /**
     * @return RouteCollection
     */
    public function getRouteCollection() {
        return $this->routeCollection;
    }
    /**
     * @param RouteCollection $value
     *
     * @return RouteConfiguration
     */
    public function setRouteCollection(RouteCollection $value) {
        $this->routeCollection = $value;
        return $this;
    }
    /**
     * @return UrlGeneratorInterface
     */
    public function getUrlGenerator() {
        return $this->urlGenerator;
    }
    /**
     * @param UrlGeneratorInterface $value
     *
     * @return RouteConfiguration
     */
    public function setUrlGenerator(UrlGeneratorInterface $value) {
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