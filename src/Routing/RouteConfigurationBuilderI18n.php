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
     * @param array  $availableLocales
     */
    public function __construct($currentLocale, array $availableLocales)
    {
        $this->currentLocale = $currentLocale;
        $this->availableLocales = $availableLocales;
        $this->validateLocaleConfiguration($this->currentLocale, $this->availableLocales);
    }

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        $config = $this->buildFromFile();

        $globalRoutingConfiguration = $this->buildLocalisedRoutingArray($config->load());
        $currentLocaleRoutingConfiguration = $globalRoutingConfiguration[$this->currentLocale];
        $routeCollection = RouteCollectionBuilder::build($currentLocaleRoutingConfiguration);
        $generator = $this->buildUrlGenerator($globalRoutingConfiguration);
        return $this->buildOutput($routeCollection, $generator);
    }

    /**
     * @param array $globalRoutingConfiguration
     *
     * @return \Symfony\Component\Routing\Generator\UrlGeneratorInterface
     */
    protected function buildUrlGenerator(array $globalRoutingConfiguration = array())
    {
        return new UrlGeneratorI18n($globalRoutingConfiguration, $this->requestContext, $this->currentLocale);
    }

    /**
     * @param array $configuration
     *
     * @return array
     */
    protected function buildLocalisedRoutingArray(array $configuration = array())
    {
        $byLocales = array();
        foreach ($this->availableLocales as $locale) {
            $byLocales[$locale] = array();
        }

        foreach ($configuration['routes'] as $name => $parameters) {
            if (isset($parameters['route']['pattern'])) {
                $pattern = $parameters['route']['pattern'];
                // Pattern changes when locale change
                if (is_array($pattern)) {
                    foreach ($pattern as $locale => $p) {
                        if (array_key_exists($locale, $byLocales)) {
                            $finalParameter = $parameters;
                            $finalParameter['route']['pattern'] = $p;
                            $byLocales[$locale][$name . '.' . $locale] = $finalParameter;
                        } else {
                            throw new \RuntimeException(sprintf("Route '%s' requires locale '%s' which is not registered as available locale", $name, $locale));
                        }
                    }
                }
                // Same pattern for all locales
                else {
                    foreach ($this->availableLocales as $locale) {
                        $byLocales[$locale][$name . '.' . $locale] = $parameters;
                    }
                }
            }
        }
        return $byLocales;
    }

    /**
     * @param string $currentLocale
     * @param array  $locales
     *
     * @return boolean
     *
     * @throws RuntimeException
     */
    protected function validateLocaleConfiguration($currentLocale, array $locales)
    {
        if (count($locales) === 0) {
            throw new \RuntimeException("Empty locale list provided");
        }

        if (!in_array($currentLocale, $locales)) {
            throw new \RuntimeException(sprintf("The current locale '%s' doesn't exist in locale list [%s]", $currentLocale, implode(',', $locales)));
        }

        if (strlen(trim($currentLocale)) == 0) {
            throw new \RuntimeException("No current locale, can't create RouteCollection");
        }

        return true;
    }
}
