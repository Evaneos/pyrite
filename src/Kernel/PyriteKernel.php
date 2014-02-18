<?php

namespace Pyrite\Kernel;

use DICIT\Config\YML;
use DICIT\Config\PHP;
use DICIT\Container;

use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\TerminableInterface;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Yaml\Yaml;

use Pyrite\Config\NullConfig;
use Pyrite\Kernel\Exception\HttpException;

class PyriteKernel implements HttpKernelInterface, TerminableInterface
{
    /**
     * Url matcher
     * 
     * @var UrlMatcherInterface
     */
    private $urlMatcher = null;
    
    /**
     * Debug mode (default is false)
     * 
     * @var debug
     */
    private $debug = false;
    
    /**
     * 
     * @var unknown
     */
    private $container;
    
    public function __construct($routingPath, $containerPath = null, $debug = false)
    {
        Debug::enable(null, $debug);
        
        $config = new NullConfig();
        
        if (null !== $containerPath && preg_match('/.*yml$/', $containerPath)) {
            $config = new YML($containerPath);
        }
        
        if (null !== $containerPath && preg_match('/.*php$/', $containerPath)) {
            $config = new PHP($containerPath);
        }
        
        $this->container  = new Container($config);
        $this->debug      = $debug;
        $this->urlMatcher = $this->buildRouter($routingPath);
    }
    
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true) {
        try {
            $parameters = $this->urlMatcher->match($request->getPathInfo());
        } catch (ResourceNotFoundException $e) {
            throw new HttpException(Response::HTTP_NOT_FOUND, null, $e);
        }

        $stack = $this->buildStack($parameters);
        
        //Run
        
        //Return $response
    }
    
    public function terminate(Request $request, Response $response)
    {
        
    }
    
    /**
     * Boot kernel
     * 
     * @return Response
     */
    public static function boot($routingPath, $containerPath = null, $debug = false) {
        try {
            $kernel = new self();
            
            $request = Request::createFromGlobals();
            $response = $kernel->handle($request, HttpKernelInterface::MASTER_REQUEST, true);
        } catch (HttpException $exception) {
            $response = new Response($exception->getMessage(), $exception->getCode());
        } catch (Exception $exception) {
            $response = new Response($exception->getMessage(), $exception->getCode() === 0 ? Response::HTTP_INTERNAL_SERVER_ERROR : $exception->getCode());
        }
        
        $response->send();
        
        if ($kernel instanceof TerminableInterface) {
            $kernel->terminate($request, $response);
        }
    }
    
    protected function buildStack($routeParameters)
    {
        
    }
    
    protected function buildRouter($routingPath)
    {
        //@TODO Caching
        $config = new YML($routingPath);
        $configuration = $config->load();
        
        //@TODO Validation ?
        
        
    }
}