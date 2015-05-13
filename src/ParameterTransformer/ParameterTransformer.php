<?php

namespace Pyrite\ParameterTransformer;

use Pyrite\Response\ResponseBag;
use Symfony\Component\HttpFoundation\Request;

interface ParameterTransformer
{
    public function before(ResponseBag $responseBag, Request $request);
}
