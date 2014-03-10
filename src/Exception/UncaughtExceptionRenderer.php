<?php

namespace Pyrite\Exception;

use Psr\Log\LoggerAwareInterface;

interface UncaughtExceptionRenderer extends LoggerAwareInterface
{
    function render(\Exception $e);
}