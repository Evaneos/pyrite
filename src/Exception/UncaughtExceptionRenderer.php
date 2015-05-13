<?php

namespace Pyrite\Exception;

use Psr\Log\LoggerAwareInterface;

interface UncaughtExceptionRenderer extends LoggerAwareInterface
{
    public function render(\Exception $e);
}
