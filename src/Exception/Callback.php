<?php

namespace \Pyrite\Exception;

interface Callback {

    public function handleException(\Exception $exception, \Pyrite\Response\ResponseBag $responseBag);
}