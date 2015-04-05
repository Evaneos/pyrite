<?php

namespace Pyrite\Templating\Twig;

use Pyrite\Response\ResponseBag;

interface Extension
{
    public function extend(\Twig_Environment $twig);
}
