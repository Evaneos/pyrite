<?php

namespace Pyrite\Response;

class ResponseBagImpl implements ResponseBag
{
    /** @var array  */
    protected $data = array();

    /** @var array  */
    protected $errors = array();

    /** @var string  */
    protected $result = '';

    /** @var int  */
    protected $resultCode = 200;

    /** @var array  */
    protected $headers = array();

    /** @var string  */
    protected $type = self::TYPE_DEFAULT;

    /** @var  Callable */
    protected $callback;

    public function set($key, $value)
    {
        $this->data[$key] = $value;
        return $this;
    }

    public function setType($type)
    {
        if(!in_array($type, array(self::TYPE_STREAMED, self::TYPE_BINARY, self::TYPE_DEFAULT))){
            throw new \InvalidArgumentException('Unknown response type');
        }

        $this->set('format', strtolower($type));
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function get($key, $defaultValue = null)
    {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }
        return $defaultValue;
    }

    public function getAll()
    {
        return $this->data;
    }

    public function has($key)
    {
        return array_key_exists($key, $this->data);
    }

    public function setResult($value)
    {
        $this->result = $value;
        return $this;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function setCallback($callback)
    {
        $this->callback = $callback;
    }

    public function getCallback()
    {
        return $this->callback;
    }

    public function setResultCode($value)
    {
        $this->resultCode = $value;
        return $this;
    }

    public function getResultCode()
    {
        return $this->resultCode;
    }

    public function addHeader($key, $value)
    {
        $this->headers[$key] = $value;
    }

    public function getHeaders()
    {
        return $this->headers;
    }
}
