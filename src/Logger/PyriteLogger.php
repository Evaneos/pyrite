<?php

namespace Pyrite\Logger;

use Monolog\Logger;

class PyriteLogger extends Logger
{
    /**
     * @param \Exception $error
     *
     * @return array
     */
    private function normalizeData(\Exception $error)
    {
        $data[] = [
            'message'        => $error->getMessage(),
            'line'           => $error->getLine(),
            'file'           => $error->getFile(),
            'trace_as_array' => $error->getTrace(),
            'type'           => get_class($error),
        ];

        if ($error->getPrevious() instanceof \Exception) {
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

    /**
     * @param string $message
     * @param array  $context
     *
     * @return bool
     */
    public function emergency($message, array $context = [])
    {
        if($message instanceof \Exception){
            $exceptions = $this->renderException($message);

            foreach($exceptions as $exception){
                $errorContext = ['exception' => $exception];
                parent::emergency($exception['message'], array_merge($context, $errorContext));
            }

            return true;
        }

        return parent::emergency($message, $context);
    }

    /**
     * @param string $message
     * @param array  $context
     *
     * @return bool
     */
    public function alert($message, array $context = [])
    {
        if($message instanceof \Exception){
            $exceptions = $this->renderException($message);

            foreach($exceptions as $exception){
                $errorContext = ['exception' => $exception];
                parent::alert($exception['message'], array_merge($context, $errorContext));
            }
        }

        return parent::alert($message, $context);
    }

    /**
     * @param string $message
     * @param array  $context
     *
     * @return bool
     */
    public function critical($message, array $context = [])
    {
        if($message instanceof \Exception){
            $exceptions = $this->renderException($message);

            foreach($exceptions as $exception){
                $errorContext = ['exception' => $exception];
                parent::error($exception['message'], array_merge($context, $errorContext));
            }

            return true;
        }

        return parent::critical($message, $context);
    }

    /**
     * @param string $message
     * @param array  $context
     *
     * @return bool
     */
    public function error($message, array $context = [])
    {
        if($message instanceof \Exception){
            $exceptions = $this->renderException($message);

            foreach($exceptions as $exception){
                $errorContext = ['exception' => $exception];
                parent::error($exception['message'], array_merge($context, $errorContext));
            }

            return true;
        }

        return parent::error($message, $context);
    }

    /**
     * @param string $message
     * @param array  $context
     *
     * @return bool
     */
    public function warning($message, array $context = [])
    {
        if($message instanceof \Exception){
            $exceptions = $this->renderException($message);

            foreach($exceptions as $exception){
                $errorContext = ['exception' => $exception];
                parent::warning($exception['message'], array_merge($context, $errorContext));
            }

            return true;
        }

        return parent::warning($message, $context);
    }
}
