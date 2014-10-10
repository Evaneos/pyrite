<?php

namespace Pyrite\PyRest;

class PyRestCollection
{
    /**
     * @var string resourceType
     */
    protected $resourceType;
    /**
     * @return string resourceType
     */
    public function getResourceType() {
        return $this->resourceType;
    }

    public function __construct($resourceType)
    {
        $this->resourceType = $resourceType;
    }
}