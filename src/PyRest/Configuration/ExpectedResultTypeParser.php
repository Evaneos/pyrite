<?php

namespace Pyrite\PyRest\Configuration;

use Symfony\Component\HttpFoundation\Request;

class ExpectedResultTypeParser implements Parser
{
    const NAME = __CLASS__;

    const ONE = 1;
    const MANY = 2;


    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function parse(Request $request)
    {
        if ($request->getMethod() != 'GET') {
            return null;
        }
        else {
            $resourceName = $request->attributes->get('resource', null);
            $resourceId = $request->attributes->get('id', null);
            if ($resourceName && $resourceId) {
                return self::ONE;
            }
            elseif ($resourceName) {
                return self::MANY;
            }
            else {
                return $this->nestedComputation($request);
            }
        }

    }

    protected function nestedComputation(Request $request)
    {
        $embed = $request->attributes->get('embed', null);
        $parentResource = $request->attributes->get('filter_resource', null);

        if ($embed && $parentResource) {
            $param = $this->container->getParameter('crud.packages.' . $parentResource);
            $vo = $param['vo'];
            $rest = $param['rest'];

            $data = $rest::getEmbeddables();
            if (array_key_exists($embed, $data)) {
                if ($data[$embed] instanceof \Pyrite\PyRest\PyRestItem) {
                    return self::ONE;
                }
                elseif ($data[$embed] instanceof \Pyrite\PyRest\PyRestCollection) {
                    return self::MANY;
                }
                else {
                    return null;
                }
            }
        }
        else {
            return null;
        }

    }
}
