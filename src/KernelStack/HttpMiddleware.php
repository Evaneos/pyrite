<?php

namespace Pyrite\KernelStack;

use Pyrite\Routing\RouteConfigurationBuilder;
use Pyrite\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

class HttpMiddleware implements HttpKernelInterface, TerminableInterface
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * @var HttpKernelInterface
     */
    protected $app;

    /**
     * @var RouteConfigurationBuilder
     */
    protected $routeBuilder;

    /**
     * HttpMiddleware constructor.
     *
     * @param HttpKernelInterface       $app
     * @param Router                    $router
     * @param RouteConfigurationBuilder $routeBuilder
     */
    public function __construct(
        HttpKernelInterface $app,
        Router $router,
        RouteConfigurationBuilder $routeBuilder
    ) {
        $this->app = $app;
        $this->router = $router;
        $this->routeBuilder = $routeBuilder;
    }

    /**
     * @param Request $request
     * @param int     $type
     * @param bool    $catch
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        $context = new RequestContext();
        $context->fromRequest($request);

        $this->routeBuilder->setRequest($request);
        $this->routeBuilder->setRequestContext($context);

        $routeCollection = $this->router->getRouteCollection();

        $configuration = $this->routeBuilder->build();
        $routeCollection->addCollection($configuration->getRouteCollection());
        $this->router->setUrlGenerator($configuration->getUrlGenerator());
        $this->router->setUrlMatcher(new UrlMatcher($routeCollection, $context));

        try {
            $request->attributes->add($this->router->match($request->getPathInfo()));
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

        $route = $routeCollection->get($request->attributes->get('_route'));
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
