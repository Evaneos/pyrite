<?php

namespace Pyrite\Layer;

use Pyrite\Response\ResponseBag;
use Pyrite\OutputBuilder\OutputBuilder;

class OutputLayer extends AbstractLayer
{
    private $outputBuilders = array();

    private $defaultOutputBuilder;

    public function __construct(OutputBuilder $defaultBuilder)
    {
        $this->defaultOutputBuilder = $defaultBuilder;
    }

    public function after(ResponseBag $bag)
    {
        $format = $bag->get('format');

        $builder = isset($this->outputBuilders[$format]) ? $this->outputBuilders[$format] : $this->defaultOutputBuilder;

        $data = $builder->buildOutput($bag);
        $headers = $builder->getHeaders($bag);
        foreach ($headers as $header) {
            header($header);
        }

        $bag->setResult($data);
    }

    public function addOutputBuilder($format, OutputBuilder $builder)
    {
        $this->outputBuilders[$format] = $builder;

        return $this;
    }
}
