<?php

namespace Pyrite\KernelStack;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\TerminableInterface;

class MultiSiteMiddleware implements HttpKernelInterface, TerminableInterface
{
    /**
     * @var HttpKernelInterface
     */
    protected $app;

    /**
     * @var ParameterBag
     */
    protected $config;

    /**
     * MultiSiteMiddleware constructor.
     *
     * @param HttpKernelInterface $app
     * @param int[]               $sites
     */
    public function __construct(HttpKernelInterface $app, ParameterBag $config)
    {
        $this->app = $app;
        $this->config = $config;
    }

    /**
     * @param Request $request
     * @param int     $type
     * @param bool    $catch
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        $host = $request->getHttpHost();

        $sites = $this->config->get('default_site_id');

        $site = str_replace('.', '_', $host);
        $sid = isset($sites[$site]) ? $sites[$site] : $sites['default'];

        $this->config->set('side_id', $sid);
        $this->config->set('current_site_id', $sid);



        return $this->app->handle($request, $type, $catch);
    }

    /**
     * @param Request  $request
     * @param Response $response
     */
    public function terminate(Request $request, Response $response)
    {
        if($this->app instanceof TerminableInterface){
            $this->app->terminate($request, $response);
        }
    }
}
