<?php

namespace Pyrite\KernelStack;

use Pyrite\Logger\LoggerFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
use Trolamine\Core\Authentication\AnonymousAuthenticationToken;
use Trolamine\Core\Authentication\BaseAuthentication;

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
        if($type === HttpKernelInterface::SUB_REQUEST){
            return $this->app->handle($request, $type, $catch);
        }

        if($request->headers->has('X-Request-Id')){
            $this->loggerFactory->addTag('request_id', $request->headers->get('X-Request-Id'));
        }

        return $this->app->handle($request, $type, $catch);
    }

    /**
     * @param Request  $request
     * @param Response $response
     */
    public function terminate(Request $request, Response $response)
    {
        $logger = $this->loggerFactory->create('app.request');

        $routeName = $request->attributes->get('_route');

        $logger->notice(sprintf('Route %s matched', $routeName), array(
            'route' => $routeName,
            'response_code' => $response->getStatusCode(),
            'response_size' => strlen($response->getContent())

        ));

        $this->loggerFactory->flushBuffer();

        if($this->app instanceof TerminableInterface){
            $this->app->terminate($request, $response);
        }
    }
}
