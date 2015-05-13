<?php

namespace Pyrite\Container;

interface Container
{
    public function bind($key, $item);
    public function setParameter($key, $value);
    public function getParameter($parameterName);
    public function get($serviceName);
    public function resolve($reference);
    public function resolveMany(array $references = null);
    public function flushRegistry();
}
