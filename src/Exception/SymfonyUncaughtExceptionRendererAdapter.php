<?php

namespace Pyrite\Exception;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Debug\ExceptionHandler;

class SymfonyUncaughtExceptionRendererAdapter implements UncaughtExceptionRenderer
{
    protected $exceptionHandler = null;
    protected $logger = null;

    public function __construct($displayStacktrace = false)
    {
        $this->exceptionHandler = new ExceptionHandler($displayStacktrace);
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
        $this->exceptionHandler->createResponse($e)->send();
    }
}
