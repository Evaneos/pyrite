<?php

namespace Pyrite\KernelStack;

use Mouf\Utils\Common\ConditionInterface\ConditionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

class ErrorMiddleware implements HttpKernelInterface, TerminableInterface
{
    /**
     * @var HttpKernelInterface
     */
    private $app;

    /**
     * @var bool|callable|ConditionInterface
     */
    private $catchExceptions;

    /**
     * @var bool|callable|ConditionInterface
     */
    private $catchErrors;

    /**
     * @var
     */
    private $whoops;

    /**
     * @Important
     * @param HttpKernelInterface $router The default router (the router we will catch exceptions from).
     * @param boolean|ConditionInterface|callable $catchExceptions Whether we should catch exception or not
     * @param boolean|ConditionInterface|callable $catchErrors Whether we should catch errors or not
     */
    public function __construct(HttpKernelInterface $app, $catchExceptions = true, $catchErrors = true) {
        $this->app = $app;
        $this->catchExceptions = $catchExceptions;
        $this->catchErrors = $catchErrors;
    }

    /**
     * @param Request $request
     * @param int     $type
     * @param bool    $catch
     *
     * @return Response
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        if ($this->resolveBool($this->catchErrors)) {
            $whoops = $this->getWhoops();
            $whoops->register();
        }

        if ($catch && $this->resolveBool($this->catchExceptions)) {
            try {
                return $this->app->handle($request, $type, false);
            } catch (\Exception $e) {
                $method = Run::EXCEPTION_HANDLER;

                ob_start();
                $whoops = $this->getWhoops();
                $whoops->$method($e);
                $response = ob_get_clean();
                $code = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR;
                return new Response($response, $code);
            }
        }else{
            return $this->app->handle($request, $type);
        }
    }

    /**
     * @param $value
     *
     * @return bool
     */
    protected function resolveBool($value)
    {
        if ($value instanceof ConditionInterface) {
            return $value->isOk($this);
        } elseif (is_callable($value)) {
            return $value();
        } else {
            return $value;
        }
    }

    /**
     * @return Run
     */
    protected function getWhoops()
    {
        if ($this->whoops === null) {
            $this->whoops = new Run();
            $this->whoops->pushHandler(new PrettyPageHandler());
        }

        return $this->whoops;
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
