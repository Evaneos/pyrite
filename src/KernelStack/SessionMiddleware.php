<?php

namespace Pyrite\KernelStack;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;

class SessionMiddleware implements HttpKernelInterface, TerminableInterface
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
     * SessionMiddleware constructor.
     *
     * @param HttpKernelInterface $app
     * @param ParameterBag        $config
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
     *
     * @return Response
     * @throws \Exception
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        $parameters = $this->config->get('cookie_parameters');

        if(!isset($parameters[$this->config->get('current_locale')])){
            throw new \Exception(sprintf('Domain %s not available', $request->attributes->get('current_locale')));
        }

        $cookieParams = $parameters[$this->config->get('current_locale')];

        if (HttpKernelInterface::MASTER_REQUEST !== $type) {
            return $this->app->handle($request, $type, $catch);
        }

        $session = new Session();
        $request->setSession($session);
        $cookies = $request->cookies;

        if ($cookies->has($session->getName())) {
            $session->setId($cookies->get($session->getName()));
        } else {
            //starts the session if no session exists
            $session->start();
            $session->migrate(false);
        }

        $session->start();

        $response = $this->app->handle($request, $type, $catch);

        if ($session && $session->isStarted()) {
            $session->save();
            $params = array_merge(
                session_get_cookie_params(),
                $cookieParams
            );

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
