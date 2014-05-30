<?php

namespace Pyrite\OutputBuilder;

class HtmlOutputBuilder implements OutputBuilder {

    public function getHeaders($data)
    {
        return array('Content-type: text/html; charset=UTF-8');
    }

    public function buildOutput($data)
    {
        return '<pre>'.print_r($data,true).'</pre>';
    }
}