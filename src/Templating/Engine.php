<?php

namespace Pyrite\Templating;

use Pyrite\Response\ResponseBag;

interface Engine
{
    /**
     * Render a template. The ResponseBag should be exposed to the view
     */
    function render($template, ResponseBag $bag);
}
