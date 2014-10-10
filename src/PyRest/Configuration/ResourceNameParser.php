<?php

namespace Pyrite\PyRest\Configuration;

use Symfony\Component\HttpFoundation\Request;

class ResourceNameParser implements Parser
{
    const NAME = __CLASS__;

    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    // protected function getServiceName($resourceName, $serviceName)
    // {
    //     $c = $this->container;
    //     $definitionNameWithPlaceHolders = $c->getParameter('crud.configuration.definition_name');
    //     $resolvedServiceName = sprintf($definitionNameWithPlaceHolders, $resourceName, $serviceName);

    //     return $resolvedServiceName;
    // }

    // protected function getParentResource($resourceName)
    // {
    //     return $this->container->get($this->getServiceName($resourceName, 'Service'));
    // }

    public function parse(Request $request)
    {
        $resourceName = $request->attributes->get('resource', null);
        if ($resourceName) {
            return $resourceName;
        }
        else {
            return $this->fetchFromNested($request);
        }
    }

    protected function fetchFromNested(Request $request)
    {
        $embed = $request->attributes->get('embed', null);
        if (!$embed) {
            throw new \Pyrite\PyRest\Exception\BadRequestException("Couldn't find the resourceName");
        }

        $parentResource = $request->attributes->get('filter_resource', null);
        if (!$parentResource) {
            throw new \Pyrite\PyRest\Exception\BadRequestException("Couldn't find the resourceName of the parent");
        }

        $param = $this->container->getParameter('crud.packages.' . $parentResource);
        $vo = $param['vo'];
        $rest = str_replace('VO', 'REST', $vo);
        $rest = $rest . 'REST';

        $data = $rest::getEmbeddables();
        if (array_key_exists($embed, $data)) {
            $resourceName = $data[$embed]->getResourceType();
            return $resourceName;
        }

        throw new \Pyrite\PyRest\Exception\BadRequestException("Couldn't find the resourceName of the parent");
    }
}