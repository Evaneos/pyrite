<?php

namespace Pyrite\Routing;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Config\Resource\ResourceInterface;

class RoutingConfigurationResource implements ResourceInterface
{
    protected $data = array();

    public function __construct(array $data = array())
    {
        $this->data = $data;
    }

    /**
     * Returns a string representation of the Resource.
     *
     * @return string A string representation of the Resource
     */
    public function __toString()
    {
        return json_encode($this->data);
    }

    /**
     * Returns true if the resource has not been updated since the given timestamp.
     *
     * @param int $timestamp The last time the resource was loaded
     *
     * @return bool True if the resource has not been updated, false otherwise
     */
    public function isFresh($timestamp)
    {
        return true;
    }

    /**
     * Returns the tied resource.
     *
     * @return mixed The resource
     */
    public function getResource()
    {
        return $this->data;
    }
}
