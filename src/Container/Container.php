<?php

namespace Pyrite\Container;

interface Container {
    function bind($key, $item);
    function setParameter($key, $value);
    function getParameter($parameterName);
    function get($serviceName);
    function resolve($reference);
    function resolveMany(array $references = null);
    function flushRegistry();
}