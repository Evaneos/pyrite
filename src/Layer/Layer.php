<?php

namespace Pyrite\Layer;

use Pyrite\Response\ResponseBag;
use Symfony\Component\HttpFoundation\Request;

interface Layer
{
    public function setNext(Layer $layer);
    public function setRequest(Request $request);
    public function setConfiguration(array $config = array());

    /**
     * @param  ResponseBag $responseBag
     * @return ResponseBag
     */
    public function handle(ResponseBag $responseBag);
}
