<?php

namespace Pyrite\PyRest\Exception;

class HTTPException extends \RuntimeException
{
    const CODE = 500;

    /**
     * @return array meta
     */
    protected $meta = array();

    public function __construct($message = "", $previous = null, array $meta = array())
    {
        parent::__construct($message, static::CODE, $previous);
    }

    /**
     * @return array meta
     */
    public function getMetas()
    {
        return $this->meta;
    }
    /**
     * @param array $value
     * @return HTTPException
     */
    public function setMetas($value)
    {
        $this->meta = $value;
        return $this;
    }
}
