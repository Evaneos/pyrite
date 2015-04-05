<?php

namespace Pyrite\Response;

interface ResponseBag
{
    const ACTION_RESULT = 'ACTION_RESULT';

    public function set($key, $value);
    public function get($key, $defaultValue = null);
    public function getAll();
    public function has($key);

    public function setResult($value);
    public function getResult();

    public function setResultCode($value);
    public function getResultCode();

    public function addHeader($key, $value);
    public function getHeaders();
}
