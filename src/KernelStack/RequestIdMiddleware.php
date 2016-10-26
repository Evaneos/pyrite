<?php

namespace Pyrite\KernelStack;

use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class RequestIdMiddleware implements HttpKernelInterface
{
    /** @var HttpKernelInterface  */
    private $app;

    /** @var string  */
    private $header;

    /** @var ParameterBag */
    private $config;

    /**
     * RequestIdMiddleware constructor.
     *
     * @param HttpKernelInterface $app
     * @param string              $header
     * @param null                $responseHeader
     * @param ParameterBag        $config
     */
    public function __construct(
        HttpKernelInterface $app,
        $header = 'X-Request-Id',
        ParameterBag $config
    ) {
        $this->app            = $app;
        $this->header         = $header;
        $this->config = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        if ( ! $request->headers->has($this->header)) {
            $uuid = (string) $uuid = Uuid::uuid4();
            $request->headers->set($this->header, $uuid);
            $this->config->set('request_id', $uuid);
        }else{
            $this->config->set('request_id', (string) $request->headers->get($this->header));
        }

        $response = $this->app->handle($request, $type, $catch);
        $response->headers->set($this->header, (string) $this->config->get('request_id'));

        return $response;
    }
}
