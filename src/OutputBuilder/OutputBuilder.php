<?php

namespace Pyrite\OutputBuilder;

interface OutputBuilder
{
    public function buildOutput($data);

    public function getHeaders($data);
}