<?php

namespace Pyrite\Kernel;

use DICIT\UnknownDefinitionException;
use Psr\Log\NullLogger;
use Pyrite\Container\Container;
use Pyrite\Factory\StackedHttpKernel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\TerminableInterface;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

/**
 * PyriteKernel
 *
 * Main kernel for a pyrite application
 */
class PyriteKernel implements HttpKernelInterface, TerminableInterface
{
    /**
     * Route collection
     *
     * @var RouteCollection
     */
    private $routeCollection;

    /**
     * DIC
     *
     * @var Container
     */
    private $container;

    /**
     * @var Pyrite\Exception\UncaughtExceptionRenderer
     */
    private $uncaughtRenderer;

    /**
     * @var Pyrite\Exception\ErrorHandler
     */
    private $errorHandler;

    /**
     * Stacked kernel
     *
     * @var \Pyrite\Stack\Template
     */
    private $stack = null;

    public function __construct(RouteCollection $routeCollection, Container $container)
    {
        // Debug::enable(null, $debug);

        $this->container       = $container;
        $this->routeCollection = $routeCollection;
        $this->logger          = new NullLogger();
    }

    /**
     * {@inheritDoc}
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        $context  = new RequestContext();
        $context->fromRequest($request);

        $urlMatcher   = new UrlMatcher($this->routeCollection, $context);

        try {
            $parameters = $urlMatcher->match($request->getPathInfo());

            //@TODO improve request bindings
            $request->attributes->replace($parameters);
        } catch (ResourceNotFoundException $e) {
            throw new NotFoundHttpException(sprintf("No route found for url \"%s\"", $request->getPathInfo()), $e);
        } catch (MethodNotAllowedException $e) {
            throw new MethodNotAllowedHttpException($e->getAllowedMethods(), sprintf("Method %s is not allowed for url \"%s\"", $request->getMethod(), $request->getPathInfo()), $e);
        }

        try{
            /** @var EventDispatcherInterface $dispatcher */
            $dispatcher = $this->container->get('KernelEventDispatcher');
            $dispatcher->dispatch(KernelEvents::REQUEST, new KernelEvent($this, $request, HttpKernelInterface::MASTER_REQUEST));
        } catch (UnknownDefinitionException $e) {

        }

        $this->stack = $this->getStack($parameters);

        return $this->stack->handle($request, $type, $catch);
    }

    /**
     * {@inheritDoc}
     */
    public function terminate(Request $request, Response $response)
    {
        if (null !== $this->stack) {
            $this->stack->terminate($request, $response);
        }
    }

    protected function configureErrors()
    {
        $uncaughtRenderer = $this->configOnCrashOutput();
        $errorHandler = $this->configPhpErrors($uncaughtRenderer);
    }

    private function configPhpErrors(\Pyrite\Exception\UncaughtExceptionRenderer $renderer = null)
    {
        try {
            $this->errorHandler = $this->container->get("AppErrorHandler");
        } catch (\Exception $e) {
            $this->errorHandler = new \Pyrite\Exception\ErrorHandlerImpl(0, false);
            $this->errorHandler->setOnFatalRenderer($renderer);
        }

        $this->errorHandler->enable();

        return $this->errorHandler;
    }

    private function configOnCrashOutput()
    {
        try {
            $this->uncaughtRenderer = $this->container->get("AppOnCrashHandler");
        } catch (\Exception $e) {
            $this->uncaughtRenderer = new \Pyrite\Exception\UncaughtExceptionRendererImpl(false);
        }

        return $this->uncaughtRenderer;
    }

    /**
     * Load kernel, handle a request from a webserver and send the response
     *
     * Utility function for the entrypoint of your application, only use when you are in a request context (from a webserver)
     */
    public static function boot(Request $request, RouteCollection $routeCollection, Container $container)
    {
        try {
            $kernel = new self($routeCollection, $container);
            $kernel->configureErrors();

            $response = $kernel->handle($request, HttpKernelInterface::MASTER_REQUEST, true);

            $response->send();

            if ($kernel instanceof TerminableInterface) {
                $kernel->terminate($request, $response);
            }
        } catch (\Exception $exception) {
            $kernel->uncaughtRenderer->render($exception);
        }
    }

    /**
     * Build a kernel for a specific route
     *
     * Using StackedHttpKernel factory for better reusability
     *
     * @param array $routeParameters Parameters of the route
     *
     * @return \Stack\StackedHttpKernel
     */
    protected function getStack($routeParameters)
    {
        $route      = $this->routeCollection->get($routeParameters['_route']);
        $dispatch   = $route->getOption('dispatch');

        $services   = array();
        $parameters = array();

        $factory = new StackedHttpKernel($this->container, $dispatch);
        list($name, $stack) = $factory->register(null, 'pyrite.root_kernel', $dispatch);

        return $stack;
    }
}
