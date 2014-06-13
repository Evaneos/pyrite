<?php

namespace Pyrite\Templating;

use Pyrite\Container\Container;
use Pyrite\Exception\TemplateNotFoundException;
use Pyrite\Response\ResponseBag;
use Pyrite\Templating\Engine;

class Renderer
{
    private $rootDir;

    private $supportedExtensions;

    public function __construct($rootDir)
    {
        $this->rootDir             = $rootDir;
        $this->supportedExtensions = array();
    }

    /**
     * Register a template engine.
     *
     * @param Engine    $engine         the template engine
     * @param string    $extensionsStr  list of supported extension, as a
     *                                  string. Extensions are separated by
     *                                  commas.
     */
    public function registerEngine(Engine $engine, $extensionsStr)
    {
        $extensions = explode(',', $extensionsStr);

        foreach ($extensions as $extension) {
            $this->supportedExtensions[$extension] = $engine;
        }
    }

    /**
     * Render a template. The ResponseBag data will be exposed to the view.
     *
     * @param string        $template   template path
     * @param ResponseBag   $bag        the response bag.
     */
    public function render($template, ResponseBag $bag)
    {
        $this->render($template, $bag->getAll());
    }

    /**
     * Render a template
     *
     * @param string        $template   template path
     * @param array         $data       data passed to the view
     */
    public function render($template, array $data)
    {
        $this->checkTemplatePath($template);

        $extension = $this->getTemplateExtension($template);

        if (!array_key_exists($extension, $this->supportedExtensions)) {
            throw new TemplateNotFoundException(sprintf("File format not supported: %s", $extension));
        }

        $engine = $this->supportedExtensions[$extension];
        return $engine->render($template, $data);
    }

    private function checkTemplatePath($template)
    {
        if (!file_exists($this->rootDir . $template)) {
            throw new TemplateNotFoundException(sprintf("Template not found: %s", $template), 500);
        }
    }

    private function getTemplateExtension($template)
    {
        return pathinfo($template, PATHINFO_EXTENSION);
    }
}
