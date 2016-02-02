<?php

namespace Pyrite\Routing;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;

class Router implements RouterInterface
{
    /**
     * @var UrlMatcherInterface
     */
    protected $matcher;

    /**
     * @var UrlGeneratorInterface
     */
    protected $generator;

    /** @var RequestContext */
    protected $context;

    /**
     * @var RouteCollection
     */
    protected $collection;

    /**
     * @var RouteConfigurationBuilder
     */
    protected $builder;

    /**
     * Router constructor.
     *
     * @param RouteCollection           $collection
     * @param RouteConfigurationBuilder $builder
     */
    public function __construct(RouteConfigurationBuilder $builder, RouteCollection $collection = null)
    {
        $this->builder = $builder;
        $this->collection = null !== $collection ? $collection : new RouteCollection();
    }

    /**
     * @return UrlMatcherInterface
     */
    public function getUrlMatcher()
    {
        return $this->matcher;
    }

    /**
     * @return UrlGeneratorInterface
     */
    public function getUrlGenerator()
    {
        return $this->generator;
    }

    /**
     * @param UrlMatcherInterface $matcher
     */
    public function setUrlMatcher(UrlMatcherInterface $matcher)
    {
        $this->matcher = $matcher;
    }

    /**
     * @param UrlGeneratorInterface $generator
     */
    public function setUrlGenerator(UrlGeneratorInterface $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @param RequestContext $context
     */
    public function setContext(RequestContext $context)
    {
        $this->context = $context;
    }

    /**
     * @return RequestContext
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return RouteCollection
     */
    public function getRouteCollection()
    {
        return $this->collection;
    }

    /**
     * @param string      $name
     * @param array       $parameters
     * @param bool|string $referenceType
     *
     * @return string
     */
    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {
        return $this->generator->generate($name, $parameters, $referenceType);
    }

    /**
     * @param string $pathinfo
     *
     * @return array
     */
    public function match($pathinfo)
    {
        return $this->matcher->match($pathinfo);
    }
}
