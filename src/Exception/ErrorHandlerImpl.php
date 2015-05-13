<?php

namespace Pyrite\Exception;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Debug\Exception\ContextErrorException;

class ErrorHandlerImpl implements ErrorHandler, LoggerAwareInterface
{
    private $levels = array(
        E_WARNING           => 'Warning',
        E_NOTICE            => 'Notice',
        E_USER_ERROR        => 'User Error',
        E_USER_WARNING      => 'User Warning',
        E_USER_NOTICE       => 'User Notice',
        E_STRICT            => 'Runtime Notice',
        E_RECOVERABLE_ERROR => 'Catchable Fatal Error',
        E_DEPRECATED        => 'Deprecated',
        E_USER_DEPRECATED   => 'User Deprecated',
        E_ERROR             => 'Error',
        E_CORE_ERROR        => 'Core Error',
        E_COMPILE_ERROR     => 'Compile Error',
        E_PARSE             => 'Parse',
    );

    protected $logger = null;
    protected $enabled = false;
    protected $convert2exception = false;
    protected $convertMinimumLevel = 0;
    protected $onFatalRenderer = null;

    public function __construct($level, $convert2exception = false)
    {
        $this->convertMinimumLevel($level);
        $this->enableExceptionConversion($convert2exception);
        $this->logger = new NullLogger();
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    public function enableExceptionConversion($bool)
    {
        $this->convert2exception = (bool) $bool;
        return $this;
    }

    public function convertMinimumLevel($level)
    {
        $this->convertMinimumLevel = (int) $level;
        return $this;
    }

    public function enable()
    {
        if (!$this->enabled) {
            $this->enabled = true;
            set_error_handler(array($this, 'handleError'));
            register_shutdown_function(array($this, 'handleFatal'), $this);
        }
        return $this;
    }

    public function disable()
    {
        if ($this->enabled) {
            $this->enabled = false;
            restore_error_handler();
            register_shutdown_function(function () { });
        }

        return $this;
    }

    public function setOnFatalRenderer(UncaughtExceptionRenderer $renderer = null)
    {
        $this->onFatalRenderer = $renderer;
        return $this;
    }

    public function handleError($level, $message, $file = 'unknown', $line = 0, $context = array())
    {
        $this->logError($level, $message, $file, $line, $context);
        if ($this->convert2exception && $level >= $this->convertMinimumLevel) {
            $exception = $this->convert2exception($level, $message, $file, $line, $context);
            throw $exception;
        }
    }

    public function handleFatal($handler)
    {
        if (null === $error = error_get_last()) {
            return;
        }

        $level = $error['type'];
        $message = $error['message'];
        $file = $error['file'];
        $line = $error['line'];

        try {
            // unrecoverable errors must be threated as uncaught exceptions, that's why we override the config
            $handler->convert2exception = true;
            $handler->convertMinimumLevel = 0;
            $handler->handleError($level, $message, $file, $line, array());
        } catch (\Exception $e) {
            if ($handler->onFatalRenderer) {
                $handler->onFatalRenderer->render($e);
            }
        }
    }

    protected function convert2exception($level, $message, $file, $line, $context = array())
    {
        $levelToString = isset($this->levels[$level]) ? $this->levels[$level] : $level;
        $exceptionMessage = sprintf('%s: %s in %s line %d', $levelToString, $message, $file, $line);
        $exception = new ContextErrorException($exceptionMessage, 0, $level, $file, $line, $context);
        return $exception;
    }

    protected function logError($level, $message, $file, $line, $context = array())
    {
        $levelToString = isset($this->levels[$level]) ? $this->levels[$level] : $level;
        $messageEnhanced = sprintf('%s: %s in %s line %d', $levelToString, $message, $file, $line);

        switch ($level) {
            case E_WARNING :
            case E_USER_WARNING :
                $this->logger->warning($messageEnhanced, $context);
                break;
            case E_NOTICE :
            case E_USER_NOTICE :
                $this->logger->notice($messageEnhanced, $context);
                break;
            case E_ERROR :
            case E_USER_ERROR :
                $this->logger->error($messageEnhanced, $context);
                break;
            case E_CORE_ERROR :
            case E_COMPILE_ERROR :
            case E_PARSE :
            case E_RECOVERABLE_ERROR :
                $this->logger->emergency($messageEnhanced, $context);
                break;
            case E_DEPRECATED :
            case E_USER_DEPRECATED :
            case E_STRICT :
                $this->logger->critical($messageEnhanced, $context);
                break;
            default :
                $this->logger->alert($messageEnhanced, $context);
                break;
        }

        return true;
    }
}
