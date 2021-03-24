<?php

namespace Pyrite\Layer;

use Pyrite\Container\Container;
use Pyrite\Layer\Executor\Executable;
use Pyrite\Response\ResponseBag;

class ExecutorLayer extends AbstractLayer
{
    /** @var Container */
    private $container;

    /**
     * ExecutorLayer constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @inheritdoc
     */
    public function handle(ResponseBag $bag)
    {
        try {
            ob_start();
            $ret = parent::handle($bag);
            ob_get_clean();
        } catch (\Exception $e) {
            ob_get_clean();
            throw $e;
        }

        return $ret;
    }

    /**
     * @inheritdoc
     *
     * @throws \RangeException
     * @throws \RuntimeException
     */
    protected function before(ResponseBag $bag)
    {
        $class = $this->getServiceNameFromConfig();

        if (!$class) {
            throw new \RuntimeException(sprintf("A class to execute is mandatory, none given"));
        }

        try {
            $classInstance = $this->container->get($class);
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf("Couldn't load '%s' from container", $class), 500, $e);
        }

        if (!($classInstance instanceof Executable)) {
            throw new \RuntimeException(sprintf("Expecting instance of Executable, %s given",
                get_class($classInstance)), 500);
        }

        $res = $classInstance->execute($this->request, $bag);

        if ($res) {
            $bag->set(ResponseBag::ACTION_RESULT, $res);
        }
    }

    /**
     * @return mixed
     *
     * @throws \RangeException
     */
    protected function getServiceNameFromConfig()
    {
        if (1 !== count($this->config)) {
            throw new \RangeException(sprintf("Number of arguments mismatch in Executor Layer, %d given",
                count($this->config)), 500);
        }

        return reset($this->config);
    }
}
