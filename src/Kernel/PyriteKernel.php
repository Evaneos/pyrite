<?php
namespace Pyrite\Kernel;

use Monolog\Logger;
use Psr\Log\NullLogger;
use Pyrite\Container\Container;
use Pyrite\Factory\StackedHttpKernel;
use Symfony\Component\Debug\Debug;
use Symfony\Component\Debug\ErrorHandler;
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
use Symfony\Component\Routing\Route;
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
        $this->container = $container;
        $this->routeCollection = $routeCollection;
        $this->logger = new NullLogger();
    }

    /**
     * Load kernel, handle a request from a webserver and send the response
     *
     * Utility function for the entrypoint of your application, only use when you are in a request context (from a
     * webserver)
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

    protected function configureErrors()
    {
        $uncaughtRenderer = $this->configOnCrashOutput();
        $errorHandler = $this->configPhpErrors($uncaughtRenderer);
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

    /**
     * {@inheritDoc}
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        $context = new RequestContext();
        $context->fromRequest($request);
        $urlMatcher = new UrlMatcher($this->routeCollection, $context);
        $dispatch = null;
        try {
            $parameters = $urlMatcher->match($request->getPathInfo());
            $dispatch = $this->getDispatchStackFromRoute($parameters);
            //@TODO improve request bindings
            $request->attributes->replace($parameters);
        } catch (ResourceNotFoundException $e) {
            $dispatch = $this->getDispatchStackFromKey('error404');
            if (!$dispatch) {
                throw new NotFoundHttpException(sprintf("No route found for url \"%s\"", $request->getPathInfo()), $e);
            }
        } catch (MethodNotAllowedException $e) {
            throw new MethodNotAllowedHttpException($e->getAllowedMethods(), sprintf("Method %s is not allowed for url \"%s\"", $request->getMethod(), $request->getPathInfo()), $e);
        }
        $this->stack = $this->getStackForDispatch($dispatch);

        return $this->stack->handle($request, $type, $catch);
    }

    /**
     * Get a Stack configuration for a specific route
     *
     * @param array $routeParameters Parameters of the route
     *
     * @return string[]
     */
    protected function getDispatchStackFromRoute($routeParameters)
    {
        $route = $this->routeCollection->get($routeParameters['_route']);
        $dispatch = $route->getOption('dispatch');

        return $dispatch;
    }

    /**
     * Get a Stack configuration for a specific config key
     *
     * @param array $routeParameters Parameters of the route
     *
     * @return string[]|null
     */
    protected function getDispatchStackFromKey($key)
    {
        $dispatch = null;
        $resources = $this->routeCollection->getResources();
        foreach ($resources as $resource) {
            if ($resource instanceof \Pyrite\Routing\RoutingConfigurationResource) {
                $rawConfig = $resource->getResource();
                if (array_key_exists($key, $rawConfig)) {
                    $route = $rawConfig[$key];
                    if (array_key_exists('dispatch', $route)) {
                        $dispatch = $route['dispatch'];
                        break;
                    }
                }
            }
        }

        return $dispatch;
    }

    /**
     * Build a kernel from a dispatch configuration
     *
     * Using StackedHttpKernel factory for better reusability
     *
     * @param string[] Array of service name to use
     *
     * @return \Stack\StackedHttpKernel
     */
    protected function getStackForDispatch($dispatch)
    {
        $factory = new StackedHttpKernel($this->container, $dispatch);
        list($name, $stack) = $factory->register(null, 'pyrite.root_kernel', $dispatch);

        return $stack;
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

    /**
     * Build a kernel for a specific route
     *
     * Using StackedHttpKernel factory for better reusability
     *
     * @param array $routeParameters Parameters of the route
     *
     * @return \Stack\StackedHttpKernel
     */
    protected function getStackForRoute($routeParameters)
    {
        $route = $this->routeCollection->get($routeParameters['_route']);
        $dispatch = $route->getOption('dispatch');
        $factory = new StackedHttpKernel($this->container, $dispatch);
        list($name, $stack) = $factory->register(null, 'pyrite.root_kernel', $dispatch);

        return $stack;
    }
}
