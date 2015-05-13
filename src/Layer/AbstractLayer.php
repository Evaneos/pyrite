<?php

namespace Pyrite\Layer;

use Pyrite\Response\ResponseBag;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractLayer implements Layer
{
    protected $wrappedLayer = null;
    protected $request = null;
    protected $config = array();

    public function setNext(Layer $layer)
    {
        $this->wrappedLayer = $layer;
        return $this;
    }

    public function setConfiguration(array $config = array())
    {
        $this->config = $config;
        return $this;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @param  ResponseBag $responseBag
     * @return ResponseBag
     */
    public function handle(ResponseBag $responseBag)
    {
        $this->before($responseBag);
        $result = $this->aroundNext($responseBag);
        $this->after($responseBag);

        return $result;
    }

    protected function before(ResponseBag $responseBag)
    {
    }

    protected function after(ResponseBag $responseBag)
    {
    }

    protected function aroundNext(ResponseBag $responseBag)
    {
        if ($this->wrappedLayer) {
            return $this->wrappedLayer->handle($responseBag);
        }

        return $responseBag;
    }
}
