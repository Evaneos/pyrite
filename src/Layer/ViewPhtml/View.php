<?php

namespace Pyrite\Layer\ViewPhtml;


class View
{
    protected $bag = null;
    protected $path = null;

    public function __construct($path, $bag)
    {
        $this->path = $path;
        $this->bag = $bag;
    }

    public function __get($key)
    {
        return $this->bag->get($key);
    }

    public function getResult($name = null)
    {
        if ($name) {
            return $this->getPlaceholderResult($name);
        } else {
            return $this->bag->getResult();
        }
    }

    protected function getPlaceholderResult($name)
    {
        $result = $this->bag->get(PhtmlRenderer::PLACEHOLDER . $name);
        if (is_scalar($result)) {
            return (string)$result;
        } else {
            return '';
        }
    }

    public function render()
    {
        if (file_exists($this->path)) {
            ob_start();
            require($this->path);
            $content = ob_get_clean();
            return $content;
        } else {
            throw new \RuntimeException('view not found');
        }
    }
}
