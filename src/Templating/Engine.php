<?php

namespace Pyrite\Templating;


interface Engine
{
    /**
     * Render a template with some data.
     */
    public function render($template, array $data);
}
