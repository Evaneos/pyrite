<?php

namespace Pyrite\Layer;

use Pyrite\OutputBuilder\OutputBuilder;
use Pyrite\Response\ResponseBag;

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

        $builder->buildOutput($bag);
    }

    public function addOutputBuilder($format, OutputBuilder $builder)
    {
        $this->outputBuilders[$format] = $builder;

        return $this;
    }
}
