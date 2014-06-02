<?php

namespace Pyrite\OutputBuilder;

use Pyrite\Response\ResponseBag;

interface OutputBuilder
{
    public function buildOutput(ResponseBag $bag);

    public function getHeaders(ResponseBag $bag);
}