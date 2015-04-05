<?php

namespace Pyrite\Routing;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UrlGeneratorI18n implements UrlGeneratorInterface
{
    /**
     * @var UrlGeneratorInterface[]
     */
    protected $generators = array();
    /**
     * @var array
     */
    protected $routingConfiguration = null;
    /**
     * @var RequestContext
     */
    protected $context = null;
    /**
     * @var string
     */
    protected $currentLocale = '';

    /**
     * @param array          $routingConfiguration
     * @param RequestContext $context
     * @param string         $currentLocale
     */
    public function __construct(array $routingConfiguration, RequestContext $context, $currentLocale)
    {
        $this->routingConfiguration = $routingConfiguration;
        $this->context = $context;
        $this->currentLocale = $currentLocale;
    }

    /**
     * @param  string $name
     * @param  array  $parameters
     * @param  mixed  $referenceType
     *
     * @return string
     *
     * @throws UrlGeneratorException
     * @throws RouteNotFoundException
     * @throws MissingMandatoryParametersException
     * @throws InvalidArgumentException
     */
    public function generate($name, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        $urlGenerator = null;
        $nameWithLocale = $name;

        $parts = explode('.', $name);
        if (count($parts) === 1) {
            $urlGenerator = $this->getUrlGenerator($this->currentLocale);
            $nameWithLocale = $name . "." . $this->currentLocale;
        } elseif (count($parts) > 1) {
            $locale = end($parts);
            $urlGenerator = $this->getUrlGenerator($locale);
        }

        return $urlGenerator->generate($nameWithLocale, $parameters, $referenceType);
    }

    /**
     * @param string $locale
     */
    public function setCurrentLocale($locale)
    {
        $this->currentLocale = $locale;
    }

    /**
     * @param RequestContext $context The context
     */
    public function setContext(RequestContext $context)
    {
        $this->context = $context;
    }

    /**
     * @return RequestContext The context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param  string $locale
     *
     * @return UrlGenerator
     */
    protected function getUrlGenerator($locale)
    {
        if (!array_key_exists($locale, $this->generators)) {
            $this->generators[$locale] = $this->buildUrlGenerator($locale);
        }

        return $this->generators[$locale];
    }

    /**
     * @param  string $locale
     *
     * @return UrlGenerator
     */
    protected function buildUrlGenerator($locale)
    {
        if (!array_key_exists($locale, $this->routingConfiguration)) {
            throw new UrlGeneratorException("Routing configuration doesn't provide any route for the '$locale' locale");
        } else {
            $routeCollection = RouteCollectionBuilder::build($this->routingConfiguration[$locale]);
            return new UrlGenerator($routeCollection, $this->context);
        }
    }
}
