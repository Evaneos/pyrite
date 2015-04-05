<?php

namespace Pyrite\Exception;

interface ErrorHandler
{
    public function handleError($level, $message, $file = 'unknown', $line = 0, $context = array());
    public function setOnFatalRenderer(UncaughtExceptionRenderer $renderer);
}
