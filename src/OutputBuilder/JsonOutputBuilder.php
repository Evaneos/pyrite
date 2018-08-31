<?php

namespace Pyrite\OutputBuilder;

use Pyrite\Response\ResponseBag;

class JsonOutputBuilder implements OutputBuilder
{
    public function buildOutput(ResponseBag $bag)
    {
        $bag->addHeader('Content-type', 'application/json; charset=UTF-8');

        $bag->setResult(json_encode($bag->get('data'), JSON_NUMERIC_CHECK));
    }
}
