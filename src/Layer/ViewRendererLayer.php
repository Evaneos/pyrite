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

    /**
     * Renders the associated action result template associated to the template property
     * Will also render a layout if:
     * - a layout property has been defined
     * - a layout has been defined for the module
     * - a layout has been defined in the common module
     *
     * If layout property === false, then no layout will be rendered
     */
    protected function renderResult($actionResult)
    {
        if (!$this->hasResult($actionResult)) {
            return;
        }

        $resultConfig = $this->config[$actionResult];


        if (array_key_exists('template', $resultConfig)) {
            $this->renderView($resultConfig['template']);
        }

        if (array_key_exists('layout', $resultConfig)) {
            if ($resultConfig['layout'] === false) {
                return;
            }
            $this->renderView($resultConfig['layout']);
        }
        elseif ($this->viewForModuleExists($this->config['module'], 'layout.phtml')) {
            $this->renderView('app/browser/' . $this->config['module'] . '/views/layout.phtml');
        }
        elseif ($this->viewForModuleExists('common', 'layout.phtml')) {
            $this->renderView('app/browser/common/views/layout.phtml');
        }
    }

    protected function viewExists($path) {
        return file_exists($this->rootDir . $path);
    }

    protected function renderView($path) {
        if (!$this->viewExists($path)) {
            return;
        }

        ob_start();
        include $this->rootDir . $path;
        $output = ob_get_clean();

        $this->bag->setResult($output);
    }

    protected function viewForModuleExists($module, $name) {
        return $this->viewExists('app/browser/' . $module . '/views/' . $name);
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
