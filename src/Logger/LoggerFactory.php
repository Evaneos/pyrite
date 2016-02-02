<?php

namespace Pyrite\Logger;

use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\TagProcessor;
use Monolog\Processor\WebProcessor;
use Psr\Log\LoggerInterface;

final class LoggerFactory
{
    /**
     * @var bool
     */
    private $debug;

    /**
     * @var string
     */
    private $logDir;

    /** @var array */
    protected $processors;

    /**
     * @var array
     */
    protected $handlers;

    /**
     * @var TagProcessor
     */
    protected $tagProcessor;

    /**
     * LoggerFactory constructor.
     *
     * @param bool $debug
     * @param string $logDir
     */
    public function __construct($debug, $logDir)
    {
        $this->debug = $debug;
        $this->logDir = $logDir;

        $webProcessor = new WebProcessor();
        $this->tagProcessor = new TagProcessor();

        $introspectionProcessor = new IntrospectionProcessor(Logger::ERROR);

        $memoryProcess = new MemoryUsageProcessor();

        $this->processors = array(
            $webProcessor,
            $this->tagProcessor,
            $introspectionProcessor,
            $memoryProcess
        );

        $path = $this->logDir.'/app.log';
        $level = true === $this->debug ? Logger::DEBUG : Logger::INFO;

        $streamHandler = new StreamHandler($path, $level, true);

        $this->handlers = array(
            $streamHandler
        );
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function addTag($name, $value)
    {
        $this->tagProcessor->addTags(array($name => $value));
    }

    /**
     * @param HandlerInterface $handler
     */
    public function addHandler(HandlerInterface $handler)
    {
        $this->handlers[] = $handler;
    }

    /**
     * @param callable $processor
     */
    public function addProcess(callable $processor)
    {
        $this->processors[] = $processor;
    }

    /**
     * @param string $channelName
     *
     * @return LoggerInterface
     */
    public function create($channelName)
    {
        $logger = new Logger($channelName, $this->handlers, $this->processors);
        return $logger;
    }
}
