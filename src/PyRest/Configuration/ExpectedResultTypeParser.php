<?php

namespace Pyrite\PyRest\Configuration;

use Symfony\Component\HttpFoundation\Request;
use Pyrite\PyRest\PyRestConfiguration;
use Pyrite\PyRest\Type\PyRestItem;
use Pyrite\PyRest\Type\PyRestProperty;
use Pyrite\PyRest\Type\PyRestCollection;


class ExpectedResultTypeParser implements Parser
{
    const NAME = __CLASS__;

    const ONE = 1;
    const MANY = 2;


    protected $builderProvider;

    public function __construct($builderProvider)
    {
        $this->builderProvider = $builderProvider;
    }

    public function parse(Request $request)
    {
        if ($request->getMethod() != 'GET') {
            return null;
        }

        $resourceName = $request->attributes->get('resource', null);
        $resourceId = $request->attributes->get('id', null);

        if ($resourceName && $resourceId) {
            return self::ONE;
        }

        if ($resourceName) {
            return self::MANY;
        }

        return $this->nestedComputation($request);
    }

    protected function nestedComputation(Request $request)
    {
        $embed = $request->attributes->get('nested', null);
        $parentResource = $request->attributes->get('filter_resource', null);

        if(!$embed || !$parentResource){
            return null;
        }

        $builder = $this->builderProvider->getBuilder($parentResource);
        $rest = $builder->getRESTFQCNImplementation();

        $data = $rest::getEmbeddables();

        if (array_key_exists($embed, $data)) {
            if ($data[$embed] instanceof PyRestItem) {
                return self::ONE;
            }
            elseif ($data[$embed] instanceof PyRestProperty) {
                return self::ONE;
            }
            elseif ($data[$embed] instanceof PyRestCollection) {
                return self::MANY;
            }
            else {
                return null;
            }
        }
    }
}
