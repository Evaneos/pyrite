<?php

namespace Pyrite\OutputBuilder;

use Pyrite\Response\ResponseBag;

class HtmlOutputBuilder implements OutputBuilder {

    public function getHeaders(ResponseBag $bag)
    {
        return array('Content-type: text/html; charset=UTF-8');
    }

    public function buildOutput(ResponseBag $bag)
    {
        return '<pre>'.print_r($bag->get('data'), true).'</pre>';
    }
}