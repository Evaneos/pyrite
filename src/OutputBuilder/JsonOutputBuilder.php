<?php

namespace Pyrite\OutputBuilder;

use Pyrite\Response\ResponseBag;

class JsonOutputBuilder implements OutputBuilder
{

    public function getHeaders(ResponseBag $bag)
    {
        return array('Content-type: application/json; charset=UTF-8');
    }

    public function buildOutput(ResponseBag $bag)
    {
        return json_encode($bag->get('data'), JSON_NUMERIC_CHECK);
    }
}
