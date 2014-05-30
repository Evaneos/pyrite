<?php

namespace Pyrite\ParameterTransformer;

use Symfony\Component\HttpFoundation\Request;
use Pyrite\Response\ResponseBag;

class FormatTransformer implements ParameterTransformer
{

    private $defautFormat = 'json';

    private $contentTypeMapping = array();

    public function __construct($defaultFormat = 'json')
    {
        $this->defaultFormat = $defaultFormat;

        $this->contentTypeMapping = array(
            'application/json' => 'json',
            'application/xml'  => 'xml',
            'text/html'        => 'html'
            );
    }

    public function before(ResponseBag $responseBag, Request $request)
    {
        if($format = $this->getFormatFromQuery($request)){
            $responseBag->set('format', $format);
            return;
        }

        if($format = $this->getFormatFromHeaders($request)){
            $responseBag->set('format', $format);
            return;
        }

        $responseBag->set('format', $this->defaultFormat);
    }

    private function getFormatFromHeaders(Request $request)
    {
        foreach($request->getAcceptableContentTypes() as $acceptableContentType) {

            foreach ($this->contentTypeMapping as $contentType => $format) {
                if($acceptableContentType === $contentType) {
                    return $format;
                }
            }
        }
    }

    private function getFormatFromQuery(Request $request)
    {
        return $request->query->get('format', null);
    }
}