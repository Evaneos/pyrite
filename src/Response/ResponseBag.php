<?php

namespace Pyrite\Response;

interface ResponseBag
{
    const ACTION_RESULT = 'ACTION_RESULT';

    const TYPE_DEFAULT  = 'DEFAULT';
    const TYPE_STREAMED = 'STREAMED';
    const TYPE_BINARY   = 'BINARY';

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return self
     */
    public function set($key, $value);

    /**
     * @param string $key
     * @param mixed  $defaultValue
     *
     * @return mixed
     */
    public function get($key, $defaultValue = null);

    /**
     * @return array
     */
    public function getAll();

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has($key);

    /**
     * @param string $value
     *
     * @return self
     */
    public function setResult($value);

    /**
     * @return string
     */
    public function getResult();

    /**
     * @param int $value
     *
     * @return self
     */
    public function setResultCode($value);

    /**
     * @return int
     */
    public function getResultCode();

    /**
     * @param string $key
     * @param string $value
     */
    public function addHeader($key, $value);

    /**
     * @return string[]
     */
    public function getHeaders();

    /**
     * @param callable $callback
     */
    public function setCallback($callback);

    /**
     * @return callable
     */
    public function getCallback();

    /**
     * @param string $type
     *
     * @throws \InvalidArgumentException
     */
    public function setType($type);

    /**
     * @return string
     */
    public function getType();
}
