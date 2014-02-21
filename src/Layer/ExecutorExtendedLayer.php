<?php

namespace Pyrite\Layer;

use Pyrite\Response\ResponseBag;

class ExecutorExtendedLayer extends AbstractLayer implements Layer
{
    const CONFIG_KEY_CLASS = 'class';
    const CONFIG_KEY_METHOD = 'method';

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

        try {
            $classInstance = $this->container->get($class);
        }
        catch(\Exception $e) {
            throw new \RuntimeException(sprintf("Couldn't load '%s' from container", $class), 500, $e);
        }

        if (!method_exists($classInstance, $method)) {
            throw new \RuntimeException(sprintf("Method '%s:%s' doesn't exist", get_class($classInstance), $method), 500);
        }

        $res = $classInstance->$method($this->request, $bag);

        if ($res) {
            $bag->set(ResponseBag::ACTION_RESULT, $res);
        }
    }

    protected function getServiceNameFromConfig()
    {
        if (array_key_exists(static::CONFIG_KEY_CLASS, $this->config)) {
            return $this->config[static::CONFIG_KEY_CLASS];
        }

        throw new \RuntimeException(sprintf("A class to execute is mandatory, none given"));
    }

    protected function getMethodFromConfig()
    {
        if (array_key_exists(static::CONFIG_KEY_METHOD, $this->config)) {
            return $this->config[static::CONFIG_KEY_METHOD];
        }

        throw new \RuntimeException(sprintf("A method to execute is mandatory, none given"));
    }
}