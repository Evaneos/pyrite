<?php

namespace Pyrite\ParameterTransformer;

use Symfony\Component\HttpFoundation\Request;
use Pyrite\Response\ResponseBag;

interface ParameterTransformer
{
    public function before(ResponseBag $responseBag, Request $request);
}
