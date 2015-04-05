<?php

namespace Pyrite\Layer;

use Pyrite\Response\ResponseBag;
use Pyrite\ParameterTransformer\ParameterTransformer;

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
