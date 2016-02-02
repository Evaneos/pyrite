<?php

namespace Pyrite\KernelStack;

use Pyrite\Kernel\PyriteKernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;

class PyriteMiddleware implements HttpKernelInterface, TerminableInterface
{
    /**
     * @var string
     */
    protected $routingConfigPath;

    /**
     * @var PyriteKernel
     */
    protected $kernel;

    /**
     * @var HttpKernelInterface
     */
    protected $app;

    /**
     * HttpMiddleware constructor.
     *
     * @param HttpKernelInterface $app
     * @param PyriteKernel        $kernel
     */
    public function __construct(
        HttpKernelInterface $app,
        PyriteKernel $kernel
    ) {
        $this->app = $app;
        $this->kernel = $kernel;
    }

    /**
     * @param Request $request
     * @param int     $type
     * @param bool    $catch
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        $container = $this->kernel->startContainer();
        $container->bind('request', $request);
        $container->bind('LoggerFactory', $this->kernel->getLoggerFactory());

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
