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
        $rest = $param['rest'];

        $data = $rest::getEmbeddables();
        if (array_key_exists($embed, $data)) {
            $embedDefinitionObject = $data[$embed];
            if ($embedDefinitionObject instanceof \Pyrite\PyRest\PyRestProperty) {
                throw new \Pyrite\PyRest\Exception\BadRequestException("Nested route for property is forbidden");
            }

            $resourceName = $data[$embed]->getResourceType();
            return $resourceName;
        }

        throw new \Pyrite\PyRest\Exception\BadRequestException("Couldn't find the resourceName of the parent");
    }
}
