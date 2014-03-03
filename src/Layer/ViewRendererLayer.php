<?php

namespace Pyrite\Layer;

use Pyrite\Response\ResponseBag;

class ViewRendererLayer extends AbstractLayer implements Layer
{
    protected $rootDir = null;

    public function setRootDir($rootDir)
    {
        $this->rootDir = $rootDir;
        return $this;
    }

    public function __get($key)
    {
        if ($this->bag->has($key)) {
            return $this->bag->get($key);
        }
        return null;
    }

    public function __isset($key)
    {
        return $this->bag->has($key);
    }

    public function after(ResponseBag $bag)
    {
        $this->bag = $bag;

        $actionResult = $bag->get(ResponseBag::ACTION_RESULT, false);

        $hasResult = !(false === $actionResult);

        if (!$hasResult && $this->hasDefaultTemplate()) {
            $bag->setResult($this->getDefaultTemplate());
            return;
        }

        if ($this->hasTemplate($actionResult)) {
            $bag->setResult($this->getTemplate($actionResult));
            return;
        }
    }

    protected function hasDefaultTemplate()
    {
        return array_key_exists('default', $this->config);
    }

    protected function getDefaultTemplate()
    {
        if ($this->hasDefaultTemplate()) {
            return $this->getTemplate('default');
        }
        return null;
    }

    protected function hasTemplate($name)
    {
        return array_key_exists($name, $this->config);
    }

    protected function getTemplate($name)
    {
        if ($this->hasTemplate($name)) {
            ob_start();
            include $this->rootDir . $this->config[$name];
            $output = ob_get_clean();
            return $output;
        }
        return null;
    }
}