<?php

namespace Pyrite\OutputBuilder;

class JsonOutputBuilder implements OutputBuilder {

    public function getHeaders($data)
    {
        return array('Content-type: application/json; charset=UTF-8');
    }

    public function buildOutput($data)
    {
        return json_encode($data);
    }
}