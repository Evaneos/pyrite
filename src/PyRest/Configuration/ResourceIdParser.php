<?php

namespace Pyrite\PyRest\Configuration;

use Symfony\Component\HttpFoundation\Request;

class ResourceIdParser implements Parser
{
    const NAME = __CLASS__;

    public function parse(Request $request)
    {
        return $request->attributes->get('id');
    }
}