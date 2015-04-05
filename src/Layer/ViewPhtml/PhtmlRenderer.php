<?php

namespace Pyrite\Layer\ViewPhtml;

use Pyrite\Response\ResponseBag;
use Pyrite\Layer\AbstractLayer;
use Pyrite\Layer\Layer;

class PhtmlRenderer extends AbstractLayer implements Layer
{
    const PLACEHOLDER_DEFAULT = "__default__";
    const PLACEHOLDER = "__placeholder__";

    protected $viewPath = '';

    public function __construct($viewPath)
    {
        $this->viewPath = $viewPath;
    }

    protected function after(ResponseBag $bag)
    {
        $out = self::PLACEHOLDER_DEFAULT;

        $template = $this->config[0];

        if (count($this->config) > 1) {
            $out = $this->config[1];
        }

        $path = $this->viewPath . '/' . $template;
        $view = new View($path, $bag);
        $content = $view->render();

        if ($out == self::PLACEHOLDER_DEFAULT) {
            $bag->setResult($content);
        } else {
            $bag->set('__placeholder__' . $out, $content);
        }
    }
}
