<?php

namespace Pyrite\PyRest;


final class PyRestObjectPrimitive extends PyRestObject
{
    const RESOURCE_NAME = null;


    protected static function initEmbeddables()
    {
        return array();
    }

    public function __construct($primitive)
    {
        $this->value = $primitive;
    }

    protected $value;
}