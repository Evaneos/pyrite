<?php

namespace \Pyrite\Exception;

class NotFoundExceptionCallback implements Callback {

    public function handleException(\Exception $exception, \Pyrite\Response\ResponseBag $responseBag)
    {
        $responseBag->setResultCode(404);

    }
}