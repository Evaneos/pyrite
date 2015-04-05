<?php

namespace Pyrite\Routing;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Generator\UrlGenerator;

class RouteConfigurationBuilderImpl extends RouteConfigurationBuilderAbstract
{
    /**
     * {@inheritDoc}
     */
    public function build()
    {
        $config = $this->buildFromFile();
        $routeCfgAsArray = $this->getRouteConfigurationFromConfig($config->load());
        $routeConfiguration = RouteCollectionBuilder::build($routeCfgAsArray);

        $urlGenerator = $this->buildUrlGenerator($routeConfiguration);
        return $this->buildOutput($routeConfiguration, $urlGenerator);
    }

    /**
     * @param array $config
     *
     * @return array
     *
     * @throws \RuntimeException
     */
    protected function getRouteConfigurationFromConfig(array $config = array())
    {
        if (array_key_exists('routes', $config)) {
            return $config['routes'];
        } else {
            throw new \RuntimeException("No key 'routes' in provided configuration array");
        }
    }

    /**
     * @param RouteCollection $collection
     *
     * @return \Symfony\Component\Routing\Generator\UrlGeneratorInterface
     */
    protected function buildUrlGenerator(RouteCollection $collection)
    {
        return new UrlGenerator($collection, $this->requestContext);
    }
}
