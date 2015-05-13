<?php

namespace Pyrite\Templating\Twig;


interface Extension
{
    public function extend(\Twig_Environment $twig);
}
