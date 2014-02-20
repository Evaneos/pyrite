<?php

namespace Pyrite\Layer;

use Pyrite\Response\ResponseBag;
use Symfony\Component\HttpFoundation\Request;

interface Layer
{
    function setNext(Layer $layer);
    function setRequest(Request $request);
    function setConfiguration(array $config = array());

    /**
     * @param  ResponseBag $responseBag
     * @return ResponseBag
     */
    function handle(ResponseBag $responseBag);
}