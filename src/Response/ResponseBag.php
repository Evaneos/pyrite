<?php

namespace Pyrite\Response;


interface ResponseBag
{
    const ACTION_RESULT = '_action_result';
    const HTTP_CODE = '_http_code';
    const EXCEPTION = '_exception';
    const VIEW = 'view';

    function set($key, $value);
    function get($key, $defaultValue = null);
    function has($key);
}