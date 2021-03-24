<?php

namespace Pyrite\Response;

class ResponseBagImpl implements ResponseBag
{
    /** @var array */
    private $data = [];

    /** @var string */
    private $result = '';

    /** @var int */
    private $resultCode = 200;

    /** @var string[] */
    private $headers = [];

    /** @var string */
    private $type = self::TYPE_DEFAULT;

    /** @var callable */
    private $callback;

    /**
     * @inheritdoc
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setType($type)
    {
        if (!in_array($type, [self::TYPE_STREAMED, self::TYPE_BINARY, self::TYPE_DEFAULT], true)) {
            throw new \InvalidArgumentException('Unknown response type');
        }

        $this->set('format', strtolower($type));
        $this->type = $type;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function get($key, $defaultValue = null)
    {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }

        return $defaultValue;
    }

    /**
     * @inheritdoc
     */
    public function getAll()
    {
        return $this->data;
    }

    /**
     * @inheritdoc
     */
    public function has($key)
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * @inheritdoc
     */
    public function setResult($value)
    {
        $this->result = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @inheritdoc
     */
    public function setCallback($callback)
    {
        $this->callback = $callback;
    }

    /**
     * @inheritdoc
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * @inheritdoc
     */
    public function setResultCode($value)
    {
        $this->resultCode = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getResultCode()
    {
        return $this->resultCode;
    }

    /**
     * @inheritdoc
     */
    public function addHeader($key, $value)
    {
        $this->headers[$key] = $value;
    }

    /**
     * @inheritdoc
     */
    public function getHeaders()
    {
        return $this->headers;
    }
}
