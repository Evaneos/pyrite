<?php

namespace Pyrite\OutputBuilder;

use Pyrite\Response\ResponseBag;

class HtmlOutputBuilder implements OutputBuilder
{
    public function buildOutput(ResponseBag $bag)
    {
        $bag->addHeader('Content-type', 'text/html; charset=UTF-8');

        $bag->setResult('<pre>'.print_r($bag->get('data'), true).'</pre>');
    }
}
