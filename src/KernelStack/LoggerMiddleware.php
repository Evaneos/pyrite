<?php

namespace Pyrite\KernelStack;

use Pyrite\Logger\LoggerFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;

class LoggerMiddleware implements HttpKernelInterface, TerminableInterface
{
    /**
     * @var HttpKernelInterface
     */
    private $app;

    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * LoggerMiddleware constructor.
     *
     * @param HttpKernelInterface $app
     * @param LoggerFactory       $loggerFactory
     */
    public function __construct(HttpKernelInterface $app, LoggerFactory $loggerFactory)
    {
        $this->app = $app;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * @param Request $request
     * @param int     $type
     * @param bool    $catch
     *
     * @return Response
     * @throws \Exception
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        $logger = $this->loggerFactory->create('app.request');

        $routeName = $request->attributes->get('_route');

        if(null === $routeName){
            return;
        }

        $logger->notice(sprintf('Route %s matched', $routeName));

        $response = $this->app->handle($request, $type, $catch);

        return $response;
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