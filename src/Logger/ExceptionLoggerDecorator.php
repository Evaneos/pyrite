<?php

namespace Pyrite\Logger;

use Psr\Log\LoggerInterface;

class ExceptionLoggerDecorator implements LoggerInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ExceptionLoggerDecorator constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    private function normalizeData(\Exception $error)
    {
        $data[] = [
            'message'        => $error->getMessage(),
            'line'           => $error->getLine(),
            'file'           => $error->getFile(),
            'trace_as_array' => $error->getTrace(),
            'type'           => get_class($error),
        ];

        if (null !== $error->getPrevious()) {
            $data = array_merge($data, $this->normalizeData($error->getPrevious()));
        }

        return $data;
    }

    /**
     * @param \Exception $e
     *
     * @return array
     */
    private function renderException(\Exception $e)
    {
        // Push the first exception raised from the tail to the head (more relevant exception)
        $exceptions = array_reverse($this->normalizeData($e));
        $exceptionId = uniqid('exception-', true);

        $exceptionNumber = count($exceptions);

        foreach($exceptions as $index => $exception){
            $currentPosition = $exceptionNumber - $index;
            $exception['id'] = $exceptionId;

            $exceptions[$index]['message'] = sprintf('%d/%d %s (%s)', $currentPosition, $exceptionNumber, $exception['message'], $exception['type']);

            foreach($exception['trace_as_array'] as $position => $trace){
                if (!isset($trace['file']) && !isset($trace['line'])) {
                    $betterTrace = sprintf(
                        '#%d %s::%s() ',
                        $position,
                        isset($trace['class']) ? $trace['class'] : '',
                        $trace['function']
                    );
                } else {
                    $betterTrace = sprintf(
                        '#%d %s[:%d] - %s::%s() ',
                        $position,
                        isset($trace['file']) ? $trace['file'] : '',
                        isset($trace['line']) ? $trace['line'] : 'N/A',
                        isset($trace['class']) ? $trace['class'] : '',
                        $trace['function']
                    );
                }

                $exceptions[$index]['trace_as_array'][$position] = $betterTrace;
            }
        }

        return $exceptions;
    }

    public function emergency($message, array $context = [])
    {
        if($message instanceof \Exception){
            $exceptions = $this->renderException($message);

            foreach($exceptions as $exception){
                $errorContext = ['exception' => $exception];
                $this->logger->emergency($exception['message'], array_merge($context, $errorContext));
            }
        }else{
            $this->logger->emergency($message, $context);
        }
    }

    public function alert($message, array $context = [])
    {
        if($message instanceof \Exception){
            $exceptions = $this->renderException($message);

            foreach($exceptions as $exception){
                $errorContext = ['exception' => $exception];
                $this->logger->alert($exception['message'], array_merge($context, $errorContext));
            }
        }else{
            $this->logger->alert($message, $context);
        }
    }

    public function critical($message, array $context = [])
    {
        if($message instanceof \Exception){
            $exceptions = $this->renderException($message);

            foreach($exceptions as $exception){
                $errorContext = ['exception' => $exception];
                $this->logger->critical($exception['message'], array_merge($context, $errorContext));
            }
        }else{
            $this->logger->critical($message, $context);
        }
    }

    /**
     * @param string $message
     * @param array  $context
     */
    public function error($message, array $context = [])
    {
        if($message instanceof \Exception){
            $exceptions = $this->renderException($message);

            foreach($exceptions as $exception){
                $errorContext = ['exception' => $exception];
                $this->logger->error($exception['message'], array_merge($context, $errorContext));
            }
        }else{
            $this->logger->error($message, $context);
        }
    }

    public function warning($message, array $context = [])
    {
        if($message instanceof \Exception){
            $exceptions = $this->renderException($message);

            foreach($exceptions as $exception){
                $errorContext = ['exception' => $exception];
                $this->logger->warning($exception['message'], array_merge($context, $errorContext));
            }
        }else{
            $this->logger->warning($message, $context);
        }
    }

    /**
     * @param string $message
     * @param array  $context
     */
    public function notice($message, array $context = [])
    {
        $this->logger->notice($message, $context);
    }

    /**
     * @param string $message
     * @param array  $context
     */
    public function info($message, array $context = [])
    {
        $this->logger->info($message, $context);
    }

    /**
     * @param string $message
     * @param array  $context
     */
    public function debug($message, array $context = [])
    {
        $this->logger->debug($message, $context);
    }

    /**
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     */
    public function log($level, $message, array $context = [])
    {
        $this->logger->log($level, $message, $context);
    }

}
