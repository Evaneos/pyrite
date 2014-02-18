<?php

namespace Pyrite\Kernel\Exception;

use Symfony\Component\HttpFoundation\Response;
class HttpException extends \RuntimeException
{
    public function __construct($code = null, $message = null, \Exception $previous = null)
    {
        if (null === $code) {
            $code = Response::HTTP_INTERNAL_SERVER_ERROR;
        }
        
        if (null === $message) {
            $message = sprintf("%S HTTP Error", $code);
        }
        
        parent::__construct($message, $code, $previous);
    }
}