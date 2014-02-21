<?php

namespace Pyrite\Layer\Executor;

use Symfony\Component\HttpFoundation\Request;

use Pyrite\Response\ResponseBag;

interface Executable {
    /**
     * @param  Request     $request The HTTP Request
     * @param  ResponseBag $bag     The Bag shared by all Layers of Pyrite
     * @return string               result identifier (success / failure / whatever / ...)
     */
    function execute(Request $request, ResponseBag $bag);
}