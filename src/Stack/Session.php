<?php

namespace Pyrite\Stack;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Session\Session as SymfonySession;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\TerminableInterface;

use Pyrite\Response\ResponseBag;

class Session implements HttpKernelInterface, TerminableInterface
{
    /**
     * Child container
     *
     * @var HttpKernelInterface
     */
    protected $app;
    /**
     * @var boolean
     */
    protected $start = false;
    /**
     * @var array
     */
    protected $cookieParams= null;

    /**
     *
     * @param HttpKernelInterface $app
     * @param string $start
     * @param array $cookieParams
     */
    public function __construct(HttpKernelInterface $app, $start = false, array $cookieParams = array())
    {
        $this->app = $app;
        $this->start = $start;
        $this->cookieParams = $cookieParams;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $type) {
            return $this->app->handle($request, $type, $catch);
        }
        $session = new SymfonySession();
        $request->setSession($session);

        if ($this->start) {
            $cookies = $request->cookies;
            if ($cookies->has($session->getName())) {
                $session->setId($cookies->get($session->getName()));
            } else {
                //starts the session if no session exists
                $session->migrate(false);
            }

            $session->start();
        }

        $response = $this->app->handle($request, $type, $catch);

        if ($session && $session->isStarted()) {
            $session->save();
            $params = array_merge(
                session_get_cookie_params(),
                $this->cookieParams
            );
            if (array_key_exists('domain', $this->cookieParams)) {
                $response->headers->clearCookie($session->getName());
            }
            $cookie = new Cookie(
                $session->getName(),
                $session->getId(),
                0 === $params['lifetime'] ? 0 : $request->server->get('REQUEST_TIME') + $params['lifetime'],
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
            $response->headers->setCookie($cookie);
        }

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function terminate(Request $request, Response $response)
    {
    }
}
