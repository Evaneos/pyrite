<?php

namespace Pyrite\Templating;

use Pyrite\Response\ResponseBag;
use Pyrite\Templating\Twig\Extension;

require_once __DIR__ . '/../../vendor/twig/twig/lib/Twig/Autoloader.php';

class TwigEngine implements Engine
{
    private $twig;

    public function __construct($rootDir) {

        \Twig_Autoloader::register();
        $loader = new \Twig_Loader_Filesystem($rootDir);
        $this->twig = new \Twig_Environment($loader, array(
            'cache' => $rootDir . '/tmp',
            'debug' => true
        ));
    }

    public function extendTwig(Extension $extension)
    {
        $extension->extend($this->twig);
    }

    public function render($template, ResponseBag $bag)
    {
       $template = $this->twig->loadTemplate($template);
       
       return $template->render($bag->getAll());
    }

}
