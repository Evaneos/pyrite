<?php

namespace Pyrite\Templating;

use Pyrite\Response\ResponseBag;

interface Engine
{
    /**
     * Render a template with some data.
     */
    public function render($template, array $data);
}
