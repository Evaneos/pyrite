<?php

namespace Pyrite\PyRest\Configuration;

use Symfony\Component\HttpFoundation\Request;
use Pyrite\PyRest\Exception\BadRequestException;
use Pyrite\PyRest\Type\PyRestProperty;

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
        $embed = $request->attributes->get('nested', null);
        if (!$embed) {
            throw new BadRequestException("Couldn't find the requested resource name");
        }

        $parentResource = $request->attributes->get('filter_resource', null);
        if (!$parentResource) {
            throw new BadRequestException("Couldn't find the resourceName of the parent");
        }

        $param = $this->container->getParameter('crud.packages.' . $parentResource);
        $vo = $param['vo'];
        $rest = $param['rest'];

        $data = $rest::getEmbeddables();
        if (array_key_exists($embed, $data)) {
            $embedDefinitionObject = $data[$embed];
            if ($embedDefinitionObject instanceof PyRestProperty) {
                throw new BadRequestException("Nested route for property is forbidden");
            }

            $resourceName = $data[$embed]->getResourceType();
            return $resourceName;
        }
        else {
            throw new BadRequestException(sprintf("Couldn't find the resource name of '%s' under '%s', maybe not declared as embed of that resource ?", $embed, $parentResource));
        }

    }
}
