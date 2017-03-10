<?php

namespace Pyrite\Errors;

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ErrorSubscription
{
    /** @var string */
    protected $routeName;

    /** @var int */
    protected $httpcode;

    /**
     * ErrorSubscription constructor.
     *
     * @param string $routeName
     * @param int $httpCode
     */
    public function __construct($routeName, $httpCode)
    {
        $this->routeName = $routeName;
        $this->httpcode = $httpCode;
    }

    /**
     * @return string
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * @param \Exception $e
     *
     * @return int
     */
    public function getHttpCode(\Exception $e = null)
    {
        if ($e instanceof HttpExceptionInterface) {
            return $e->getStatusCode();
        }

        return $this->httpcode;
    }
}
