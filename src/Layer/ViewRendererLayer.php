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

        $hasActionResult = !(false === $actionResult);

        if (!$hasActionResult) {
            $bag->renderDefaultResult();
            return;
        }

        $this->renderResult($actionResult);
    }

    protected function renderDefaultResult()
    {
        $this->renderResult('default');
    }

    protected function hasResult($name)
    {
        return array_key_exists($name, $this->config);
    }

    protected function renderResult($actionResult)
    {
        if (!$this->hasResult($actionResult)) {
            return;
        }

        foreach (array_reverse($this->config[$actionResult]) as $view) {
            $this->renderView($view);
        }
    }

    protected function renderView($view) {
        ob_start();
        include $this->rootDir . $view;
        $output = ob_get_clean();

        $this->bag->setResult($output);
    }

    /**
     * An helper to return the current ResponseBag result
     * Usefull in templates if you want to echo a previously
     * returned content.
     */
    private function result() {
        return $this->bag->getResult();
    }
}
