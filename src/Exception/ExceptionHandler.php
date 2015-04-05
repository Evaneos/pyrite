<?php

namespace Pyrite\Exception;

interface ExceptionHandler
{
    public function handleException(\Exception $exception, \Pyrite\Response\ResponseBag $responseBag);
}
