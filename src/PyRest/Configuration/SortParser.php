<?php

namespace Pyrite\PyRest\Configuration;

use Symfony\Component\HttpFoundation\Request;

class SortParser implements Parser
{
    const NAME = __CLASS__;

    public function parse(Request $request)
    {
        return array();
    }
}