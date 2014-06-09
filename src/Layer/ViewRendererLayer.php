<?php

namespace Pyrite\Layer;

use Pyrite\Response\ResponseBag;
use Pyrite\Templating\Renderer;

class ViewRendererLayer extends AbstractLayer implements Layer
{
    protected $rootDir = null;

    private $templateRenderer;

    public function __construct(Renderer $templateRenderer)
    {
        $this->templateRenderer = $templateRenderer;
    }

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

        if (!$hasActionResult && $this->hasDefaultTemplate()) {
            $bag->setResult($this->getDefaultTemplate());
            return;
        }

        if ($this->hasTemplate($actionResult)) {
            $bag->setResult($this->renderTemplate($actionResult));
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
            return $this->renderTemplate('default');
        }
        return null;
    }

    protected function hasTemplate($name)
    {
        return array_key_exists($name, $this->config);
    }

    protected function renderTemplate($name)
    {
        if ($this->hasTemplate($name)) {
            return $this->templateRenderer->render($this->config[$name], $this->bag);
        }
        return null;
    }
}
