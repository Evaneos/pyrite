<?php

namespace Pyrite\Templating;

use Pyrite\Container\Container;
use Pyrite\Response\ResponseBag;
use Pyrite\Templating\Twig\Extension;

class TwigEngine implements Engine
{
    private $twig;

    public function __construct(Container $container) {
        $rootDir        = $container->getParameter('root_dir');
        $productionMode = $container->getParameter('production_mode');

        \Twig_Autoloader::register();
        $loader = new \Twig_Loader_Filesystem($rootDir);
        $this->twig = new \Twig_Environment($loader, array(
            'cache' => $rootDir . '/tmp',
            'debug' => !($productionMode === true)
        ));
    }

    public function extendTwig(Extension $extension)
    {
        $extension->extend($this->twig);
    }

    public function render($template, array $data)
    {
       $template = $this->twig->loadTemplate($template);

       return $template->render($data);
    }

}
