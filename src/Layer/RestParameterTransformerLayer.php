<?php

namespace Pyrite\Layer;

use Pyrite\ParameterTransformer\ParameterTransformer;
use Pyrite\Response\ResponseBag;

class RestParameterTransformerLayer extends AbstractLayer
{

    protected $parameterTransformers = array();

    public function addParamaterTransformer(ParameterTransformer $parameterTransformer)
    {
        $this->parameterTransformers[] = $parameterTransformer;
    }

    protected function before(ResponseBag $responseBag)
    {
        foreach ($this->parameterTransformers as $transformer) {
            $transformer->before($responseBag, $this->request);
        }
    }
}
