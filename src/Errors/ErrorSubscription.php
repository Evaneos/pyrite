<?php

namespace Pyrite\Errors;

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
     * @return int
     */
    public function getHttpCode()
    {
        return $this->httpcode;
    }
}
