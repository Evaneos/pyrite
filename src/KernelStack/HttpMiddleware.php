<?php

namespace Pyrite\KernelStack;

use Pyrite\Kernel\PyriteKernel;
use Pyrite\Routing\RouteConfigurationBuilderI18n;
use Pyrite\Routing\RouteConfigurationBuilderImpl;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
use Pyrite\Routing\Director;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;

class HttpMiddleware implements HttpKernelInterface, TerminableInterface
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
     * @param                     $routingConfigPath
     */
    public function __construct(
        HttpKernelInterface $app,
        PyriteKernel $kernel,
        $routingConfigPath
    ) {
        $this->app = $app;
        $this->kernel = $kernel;
        $this->routingConfigPath = $routingConfigPath;
    }

    /**
     * @param Request $request
     * @param int     $type
     * @param bool    $catch
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        // Create container and freeze config
        $container = $this->kernel->startContainer();

        $director = new Director($request, $this->routingConfigPath);

        $config = $this->kernel->getConfig();

        $currentLocal = $config->get('current_locale');
        $availableLocal = $config->get('available_locales');

        if(null !== $currentLocal && null !== $availableLocal){
            $routerBuilder = new RouteConfigurationBuilderI18n(
                $currentLocal,
                $availableLocal
            );
        }else{
            $routerBuilder = new RouteConfigurationBuilderImpl();
        }

        $routeConfiguration = $director->build($routerBuilder);
        $urlMatcher = new UrlMatcher($routeConfiguration->getRouteCollection(), $director->getRequestContext());
        $container->bind('UrlMatcher', $urlMatcher);
        $container->bind('UrlGenerator', $routeConfiguration->getUrlGenerator());
        $container->bind('request', $request);

        $exception = null;

        /** @var UrlMatcherInterface $urlMatcher */
        $urlMatcher = $container->get('UrlMatcher');

        try {
            $request->attributes->replace($urlMatcher->match($request->getPathInfo()));
        } catch (ResourceNotFoundException $e) {
            throw new NotFoundHttpException(sprintf(
                'No route found for url "%s"',
                $request->getPathInfo()),
                $e
            );
        } catch (MethodNotAllowedException $e) {
            $message = sprintf(
                'Method %s is not allowed for url "%s"',
                $request->getMethod(),
                $request->getPathInfo())
            ;

            throw new MethodNotAllowedHttpException($e->getAllowedMethods(), $message, $e);
        }

        $route = $routeConfiguration->getRouteCollection()->get($request->attributes->get('_route'));
        $request->attributes->set('dispatch', $route->getOption('dispatch'));

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
