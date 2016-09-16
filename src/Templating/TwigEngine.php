<?php

namespace Pyrite\Templating;

use Symfony\Bridge\Twig\Extension\DumpExtension;
use Pyrite\Container\Container;
use Pyrite\Templating\Twig\Extension;
use Symfony\Component\VarDumper\Cloner\VarCloner;

class TwigEngine implements Engine
{
    private $twig;

    public function __construct(Container $container)
    {
        $rootDir        = $container->getParameter('root_dir');
        $productionMode = $container->getParameter('production_mode');
        $debug = !($productionMode === true);

        $loader = new \Twig_Loader_Filesystem($rootDir);
        $this->twig = new \Twig_Environment($loader, array(
            'cache' => $rootDir . '/tmp',
            'debug' => $debug
        ));

        if ($debug && class_exists('Symfony\Component\VarDumper\Cloner\VarCloner') && class_exists('Symfony\Bridge\Twig\Extension\DumpExtension')) {
            $this->twig->addExtension(new DumpExtension(new VarCloner()));
        }
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
