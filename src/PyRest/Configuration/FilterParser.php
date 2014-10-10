<?php

namespace Pyrite\PyRest\Configuration;

use Symfony\Component\HttpFoundation\Request;

class FilterParser implements Parser
{
    const NAME = __CLASS__;

    const FILTER_BY_RESOURCE_NAME = 'filter_resource';
    const FILTER_BY_RESOURCE_ID = 'filter_id';

    public function parse(Request $request)
    {
        $filters = $request->query->all();
        $parsedFilters = array();

        foreach ($filters as $filterKey => $value) {
            // @TODO ask Charles why ?
            $parsedFilters[str_replace('_','.', $filterKey)] = $value;
        }

        $attr = $request->attributes;
        $filterResource = $attr->get(self::FILTER_BY_RESOURCE_NAME, null);
        $filterResourceId = $attr->get(self::FILTER_BY_RESOURCE_ID, null);

        if($filterResource) {
            $parsedFilters[self::FILTER_BY_RESOURCE_NAME] = $filterResource;
        }

        if($filterResourceId) {
            $parsedFilters[self::FILTER_BY_RESOURCE_ID] = $filterResourceId;
        }

        return $parsedFilters;
    }

}