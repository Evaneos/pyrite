<?php

namespace Pyrite\Layer;

use Pyrite\Response\ResponseBag;

class ExecutorLayer extends AbstractLayer implements Layer
{
    const DEFAULT_METHOD = 'execute';

    protected $container = null;

    public function __construct(\DICIT\Container $container)
    {
        $this->container = $container;
    }

    public function handle(ResponseBag $bag)
    {
        try {
            ob_start();
            $ret = parent::handle($bag);
            ob_get_clean();
        }
        catch(\Exception $e) {
            ob_get_clean();
            throw $e;
        }
        return $ret;
    }

    protected function before(ResponseBag $bag)
    {
        $class = $this->getServiceNameFromConfig();
        $method = $this->getMethodFromConfig();

        if (!$class) {
            throw new \RuntimeException(sprintf("A class to execute is mandatory, none given"));
        }

        if (!$method) {
            $method = self::DEFAULT_METHOD;
        }

        $classInstance = $this->container->get($class);

        if (!method_exists($classInstance, $method)) {
            throw new \RuntimeException(sprintf("Couldn't find a method to execute"));
        }

        $res = $classInstance->$method($this->request, $bag);
        $bag->set(ResponseBag::ACTION_RESULT, $res);
    }

    protected function getServiceNameFromConfig()
    {
        if (array_key_exists('class', $this->config)) {
            return $this->config['class'];
        }
        return null;
    }

    protected function getMethodFromConfig()
    {
        if (array_key_exists('method', $this->config)) {
            return $this->config['method'];
        }
        return null;
    }
}