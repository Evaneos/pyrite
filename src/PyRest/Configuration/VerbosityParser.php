<?php

namespace Pyrite\PyRest\Configuration;

use Symfony\Component\HttpFoundation\Request;

class VerbosityParser implements Parser
{
    const NAME = __CLASS__;

    const VERBOSE_YES = 1;
    const VERBOSE_NO  = 2;

    public function parse(Request $request)
    {
        $verbosity = (bool)$request->get('verbose', self::VERBOSE_YES);
        return $verbosity ? self::VERBOSE_YES : self::VERBOSE_NO;
    }
}
