<?php

namespace Pyrite\KernelStack;

use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class RequestIdMiddleware implements HttpKernelInterface
{
    /** @var HttpKernelInterface  */
    private $app;

    /** @var string  */
    private $header;

    /** @var string|null  */
    private $responseHeader;

    /** @var string */
    private $uuid;

    /**
     * RequestIdMiddleware constructor.
     *
     * @param HttpKernelInterface $app
     * @param string              $header
     * @param string|null                $responseHeader
     * @param string|null  $uuid
     */
    public function __construct(
        HttpKernelInterface $app,
        $header = 'X-Request-Id',
        $responseHeader = null,
        $uuid = null
    ) {
        $this->app            = $app;
        $this->header         = $header;
        $this->responseHeader = $responseHeader;
        $this->uuid = $this->uuid = (string) (null === $uuid ? Uuid::uuid4() : $uuid);
    }

    /**
     * {@inheritDoc}
     */
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        if ( ! $request->headers->has($this->header)) {
            $request->headers->set($this->header, $this->uuid);
        }

        $response = $this->app->handle($request, $type, $catch);

        if (null !== $this->responseHeader) {
            $response->headers->set($this->responseHeader, $request->headers->get($this->header));
        }

        return $response;
    }

    /**
     * @param string $header
     */
    public function enableResponseHeader($header = 'X-Request-Id')
    {
        $this->responseHeader = $header;
    }
}
