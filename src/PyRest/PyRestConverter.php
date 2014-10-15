<?php

namespace Pyrite\PyRest;

class PyRestConverter
{
    protected $container = null;

    public function setContainer($container)
    {
        $this->container = $container;
    }

    protected function getServiceName($resourceName, $serviceName)
    {
        $c = $this->container;
        $definitionNameWithPlaceHolders = $c->getParameter('crud.configuration.definition_name');
        $resolvedServiceName = sprintf($definitionNameWithPlaceHolders, $resourceName, $serviceName);

        return $resolvedServiceName;
    }

    protected function getBertheRESTBuilder($resourceName)
    {
        return $this->container->get($this->getServiceName($resourceName, 'RESTBuilder'));
    }

    public function convertMany($resourceName, array $data = array(), array $embeds = array())
    {
        $builder = $this->getBertheRESTBuilder($resourceName);

        list($converted, , ) = $builder->convertMany($data, $embeds);

        return array_values($converted);
    }

    public function convertOne($resourceName, $object, array $embeds = array())
    {
        $builder = $this->getBertheRESTBuilder($resourceName);

        list($converted, , ) = $builder->convertMany(array($object), $embeds);
        $values = array_values($converted);
        return reset($values);
    }
}