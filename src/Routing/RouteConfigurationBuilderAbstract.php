<?php

namespace Pyrite\Routing;

use DICIT\Config\PHP;
use DICIT\Config\YML;
use Pyrite\Config\NullConfig;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class RouteConfigurationBuilderAbstract implements RouteConfigurationBuilder
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
    protected $path = null;

    /**
     * {@inheritDoc}
     */
    abstract public function build();

    /**
     * {@inheritDoc}
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * {@inheritDoc}
     */
    public function setRequestContext(RequestContext $requestContext)
    {
        $this->requestContext = $requestContext;
    }

    /**
     * {@inheritDoc}
     */
    public function setConfigurationPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return DICIT\Config\AbstractConfig
     */
    protected function buildFromFile()
    {
        if (null !== $this->path && preg_match('/.*php$/', $this->path)) {
            $config = new PHP($this->path);
        } elseif (null !== $this->path && preg_match('/.*yml$/', $this->path)) {
            $config = new YML($this->path);
        } else {
            $config = new NullConfig();
        }

        return $config;
    }

    /**
     * @param RouteCollection       $collection
     * @param UrlGeneratorInterface $generator
     *
     * @return RouteConfiguration
     */
    protected function buildOutput(RouteCollection $collection, UrlGeneratorInterface $generator)
    {
        $routeConfiguration = new RouteConfiguration($collection, $generator);
        return $routeConfiguration;
    }
}
