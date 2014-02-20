<?php

namespace Pyrite\Stack;

use DICIT\Container;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\TerminableInterface;

use Pyrite\Response\ResponseBag;

class Controller implements HttpKernelInterface, TerminableInterface
{
    /**
     * Controller used
     *
     * @var object
     */
    private $controller;

    /**
     * Method used
     *
     * @var string
     */
    private $method;

    /**
     * Child container
     *
     * @var HttpKernelInterface
     */
    private $app;

    /**
     * Child container
     *
     * @var ResponseBag
     */
    private $responseBag;

    public function __construct(ResponseBag $responseBag, $controller, $method, HttpKernelInterface $app = null)
    {
        $this->app         = $app;
        $this->controller  = $controller;
        $this->method      = $method;
        $this->responseBag = $responseBag;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        $method   = $this->method;
        $response = $this->controller->$method($request);

        if (null !== $this->app) {
            return $this->app->handle($request, $type, $catch);
        }

        if ($response instanceof Response) {
            return $response;
        }
        else {
            return new Response('', 200);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function terminate(Request $request, Response $response)
    {
        if (null !== $this->controller && $this->controller instanceof TerminableInterface) {
            $this->controller->terminate($request, $response);
        }
    }
}
