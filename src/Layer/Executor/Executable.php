<?php

namespace Pyrite\Layer\Executor;

use Pyrite\Response\ResponseBag;
use Symfony\Component\HttpFoundation\Request;

interface Executable
{
    /**
     * @param  Request     $request The HTTP Request
     * @param  ResponseBag $bag     The Bag shared by all Layers of Pyrite
     *
     * @return string      result identifier (success / failure / whatever / ...)
     *
     * @throws \Exception
     */
    public function execute(Request $request, ResponseBag $bag);
}
