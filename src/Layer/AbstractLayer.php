<?php

namespace Pyrite\Layer;

use Pyrite\Response\ResponseBag;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractLayer implements Layer
{
    /** @var Layer|null */
    protected $wrappedLayer = null;

    protected $request = null;

    protected $config = [];

    public function setNext(Layer $layer)
    {
        $this->wrappedLayer = $layer;

        return $this;
    }

    public function setConfiguration(array $config = [])
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
     * @param ResponseBag $bag
     *
     * @return ResponseBag
     *
     * @throws \Exception
     */
    public function handle(ResponseBag $bag)
    {
        $this->before($bag);
        $result = $this->aroundNext($bag);
        $this->after($bag);

        return $result;
    }

    /**
     * @param ResponseBag $bag
     */
    protected function before(ResponseBag $bag)
    {
    }

    /**
     * @param ResponseBag $bag
     */
    protected function after(ResponseBag $bag)
    {
    }

    /**
     * @param ResponseBag $bag
     *
     * @return ResponseBag
     */
    protected function aroundNext(ResponseBag $bag)
    {
        if ($this->wrappedLayer) {
            return $this->wrappedLayer->handle($bag);
        }

        return $bag;
    }
}
