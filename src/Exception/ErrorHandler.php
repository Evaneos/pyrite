<?php

namespace Pyrite\Exception;


interface ErrorHandler
{
    function handleError($level, $message, $file = 'unknown', $line = 0, $context = array());
    function setOnFatalRenderer(UncaughtExceptionRenderer $renderer);
}