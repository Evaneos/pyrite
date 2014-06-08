<?php

namespace Pyrite\Response;


class ResponseBagImpl implements ResponseBag
{
    protected $data = array();
    protected $errors = array();
    protected $result = "";
    protected $resultCode = 200;
    protected $headers = array();

    public function set($key, $value)
    {
        $this->data[$key] = $value;
        return $this;
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
