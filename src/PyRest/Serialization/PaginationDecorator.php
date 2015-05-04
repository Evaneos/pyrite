<?php

namespace Pyrite\PyRest\Serialization;

use Pyrite\PyRest\PyRestObject;
use Pyrite\PyRest\PyRestUrlGenerator;

use Pyrite\PyRest\Configuration\ResourceNameParser;
use Pyrite\PyRest\Configuration\EmbedParser;
use Pyrite\PyRest\Configuration\PaginationParser;
use Pyrite\PyRest\PyRestConfiguration;

use Symfony\Component\HttpFoundation\Request;

class PaginationDecorator implements Serializer
{
    const OPTS_TOTAL = 'total';

    protected $wrapped = null;
    protected $config = null;
    protected $urlGenerator = null;

    public function __construct(Serializer $serializer, PyRestConfiguration $config, PyRestUrlGenerator $urlGenerator)
    {
        $this->wrapped = $serializer;
        $this->config = $config;
        $this->urlGenerator = $urlGenerator;
    }

    public function serializeMany(array $objects = array(), array $options = array())
    {
        $data = $this->wrapped->serializeMany($objects, $options);

        $count = count($objects);
        $totalCount = array_key_exists('total', $options) ? $options['total'] : null;

        $out = array(
            'data' => $data,
            'meta' => $this->addPagination($count, $totalCount)
        );

        return $out;
    }

    public function serializeOne(PyRestObject $object, array $options = array())
    {
        return $this->wrapped->serializeOne($object, $options);
    }

    protected function addPagination($count, $totalCount)
    {
        $restConfiguration = $this->config;
        $paginationConfig = $restConfiguration->getConfig(PaginationParser::NAME, array(
            PaginationParser::KEY_PAGE => PaginationParser::DEFAULT_PAGE,
            PaginationParser::KEY_NBBYPAGE => PaginationParser::DEFAULT_NB_RESULT_PER_PAGE)
        );

//        $resourceName = $restConfiguration->getConfig(ResourceNameParser::NAME);
        $page = $paginationConfig[PaginationParser::KEY_PAGE];
        $nbElements = $paginationConfig[PaginationParser::KEY_NBBYPAGE];
        $nbTotalElements = $totalCount;
        $nbElementsCurrentPage = $count;
        $nbPages = ceil($nbTotalElements / $nbElements);

        $output = array(
            'total' => $nbTotalElements,
            'count' => $nbElementsCurrentPage,
            'perPage' => $nbElements,
            'currentPage' => $page,
            'totalPage' => $nbPages,
            'links' => array()
        );

        $urlBuilder = function($page, Request $request) {
            $urlParameter = $request->query->all();
            $urlParameter[PaginationParser::KEY_PAGE] = $page;
            return $request->getSchemeAndHttpHost() . $request->getPathInfo() . '?' . urldecode(http_build_query($urlParameter));
        };

        $request = $this->config->getRequest();

        $output['links']['first'] = $urlBuilder(1, $request);
        $output['links']['last'] = $urlBuilder($nbPages, $request);

        if ($page > 1) {
            $output['links']['previous'] = $urlBuilder($page-1, $request);
        }

        $output['links']['current'] = $urlBuilder($page, $request);

        if ($page < $nbPages) {
            $output['links']['next'] = $urlBuilder($page+1, $request);
        }

        return $output;
    }
}