<?php

namespace Pyrite\PyRest\Configuration;

use Symfony\Component\HttpFoundation\Request;


interface Parser
{
    function parse(Request $string);
}