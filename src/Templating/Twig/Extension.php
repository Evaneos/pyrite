<?php

namespace Pyrite\Templating\Twig;

use Pyrite\Response\ResponseBag;

interface Extension
{
    function extend(\Twig_Environment $twig);
}
