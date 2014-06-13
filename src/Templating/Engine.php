<?php

namespace Pyrite\Templating;

use Pyrite\Response\ResponseBag;

interface Engine
{
    /**
     * Render a template with some data.
     */
    function render($template, array $data);
}
