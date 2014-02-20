<?php

namespace Pyrite\Response;


class ResponseBagImpl implements ResponseBag
{
    protected $data = array();

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

    public function has($key)
    {
        return array_key_exists($key, $this->data);
    }
}