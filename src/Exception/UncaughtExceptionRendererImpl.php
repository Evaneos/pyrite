<?php

namespace Pyrite\Exception;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Debug\Exception\FlattenException;

class UncaughtExceptionRendererImpl implements UncaughtExceptionRenderer
{
    protected $displayStacktrace = false;
    protected $logger = null;

    public function __construct($displayStacktrace = false)
    {
        $this->displayStacktrace = $displayStacktrace;
        $this->logger = new NullLogger();
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    public function render(\Exception $e)
    {
        $this->logger->emergency($e->getMessage());

        if (!$e instanceof FlattenException) {
            $e = FlattenException::create($e);
        }

        $this->renderHeaders($e);
        $this->renderBody($e);
    }

    protected function renderHeaders(FlattenException $exception)
    {
        header(sprintf('HTTP/1.0 %s', $exception->getStatusCode()));

        foreach ($exception->getHeaders() as $name => $value) {
            header($name . ': ' . $value, false);
        }
    }

    protected function renderBody(FlattenException $e)
    {
        if ($this->displayStacktrace) {
            $exceptions = array();
            $exceptions[] = $e;
            $all = array_merge($exceptions, $e->getAllPrevious());

            foreach ($all as $exception) {
                sprintf("%s on %s:%s\n%s\n", $exception->getMessage(), $exception->getFile(), $exception->getLine(), $exception->getTraceAsString());
            }
        }
    }
}
