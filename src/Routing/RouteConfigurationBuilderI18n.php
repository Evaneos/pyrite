<?php

namespace Pyrite\Routing;

use Pyrite\Config\NullConfig;
use DICIT\Config\YML;
use DICIT\Config\PHP;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

class RouteConfigurationBuilderI18n extends RouteConfigurationBuilderAbstract
{
    /**
     * @var string
     */
    protected $currentLocale = null;
    /**
     * @var array
     */
    protected $availableLocales = null;

    /**
     * @param string $currentLocale
     * @param array|null $availableLocales
     */
    public function __construct($currentLocale, array $availableLocales = null)
    {
        $this->currentLocale = $currentLocale;
        $this->availableLocales = $availableLocales;
    }

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        $config = $this->buildFromFile();
        $globalRoutingConfiguration = $this->buildLocaleObject($config->load());
        $currentLocaleRoutingConfiguration = $globalRoutingConfiguration[$this->currentLocale];
        $routeCollection = RouteCollectionBuilder::build($currentLocaleRoutingConfiguration);
        $generator = $this->buildUrlGenerator($globalRoutingConfiguration);
        return $this->buildOutput($routeCollection, $generator);
    }

    /**
     * @param  array  $globalRoutingConfiguration
     * @return \Symfony\Component\Routing\Generator\UrlGeneratorInterface
     */
    protected function buildUrlGenerator(array $globalRoutingConfiguration = array())
    {
        return new UrlGeneratorI18n($globalRoutingConfiguration, $this->requestContext, $this->currentLocale);
    }

    /**
     * @param  array  $configuration
     * @return array
     */
    protected function buildLocaleObject(array $configuration = array())
    {
        if (is_array($this->availableLocales) && count($this->availableLocales) > 0) {
            $locales = $this->availableLocales;
        }
        else {
            $locales = $this->extractLocales($configuration);
        }

        $routesByLocales = $this->extractByLocales($configuration, $locales);

        $this->validateLocaleConfiguration($locales, $this->currentLocale);

        return $routesByLocales;
    }

    /**
     * When current object doesn't know locales, fetch them from the routing configuration file
     * @param  array  $configuration
     * @return array
     */
    protected function extractLocales(array $configuration = array())
    {
        $locales = array();
        foreach($configuration['routes'] as $name => $parameters) {
            if (isset($parameters['route']['pattern'])) {
                $pattern = $parameters['route']['pattern'];
                if (is_array($pattern)) {
                    foreach($pattern as $locale => $p) {
                        $locales[$locale] = null;
                    }
                }
            }
        }

        return array_keys($locales);
    }

    /**
     * Returns the routes sorted by locale, with name localised
     * @param  array  $configuration
     * @param  array  $locales
     * @return array
     */
    protected function extractByLocales(array $configuration = array(), $locales = array())
    {
        $byLocales = array();
        foreach($locales as $locale) {
            $byLocales[$locale] = array();
        }

        foreach($configuration['routes'] as $name => $parameters) {
            if (isset($parameters['route']['pattern'])) {
                $pattern = $parameters['route']['pattern'];
                // Pattern changes when locale change
                if (is_array($pattern)) {
                    foreach($pattern as $locale => $p) {
                        $finalParameter = $parameters;
                        $finalParameter['route']['pattern'] = $p;
                        $byLocales[$locale][$name . '.' . $locale] = $finalParameter;
                    }
                }
                // Same pattern for all locales
                else {
                    foreach($locales as $locale) {
                        $byLocales[$locale][$name . '.' . $locale] = $parameters;
                    }
                }
            }
        }
        return $byLocales;
    }

    /**
     * @param  array $locales
     * @param  string $currentLocale
     * @return boolean
     * @throws RuntimeException
     */
    protected function validateLocaleConfiguration(array $locales, $currentLocale)
    {
        if ($currentLocale === null && count($locales) > 1) {
            throw new \RuntimeException('Routing configuration contains multiple locales, none provided to the router builder');
        }
        if (!in_array($currentLocale, $locales)) {
            throw new \RuntimeException("The current locale doesn't exist in routing configuration files");
        }

        return true;
    }
}