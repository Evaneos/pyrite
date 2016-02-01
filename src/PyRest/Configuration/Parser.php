<?php

namespace Pyrite\PyRest\Configuration;

use Symfony\Component\HttpFoundation\Request;

interface Parser
{
    public function parse(Request $string);
}
