<?php

namespace Pyrite\Logger;

use Monolog\Handler\BufferHandler;
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
     * @var LoggerInterface[]
     */
    private $loggers;

    /**
     * @var BufferHandler
     */
    private $bufferHandler;

    /** @var array */
    private $tags;

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
        $this->loggers = array();
        $this->tags = array();

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

        if(!file_exists($this->logDir)){
            mkdir($this->logDir, 0777, true);
        }

        $path = $this->logDir.'/app.log';
        $level = true === $this->debug ? Logger::DEBUG : Logger::INFO;

        $this->bufferHandler = new BufferHandler(new StreamHandler($path, $level, true));

        $this->handlers[] = $this->bufferHandler;
    }

    public function flushBuffer()
    {
        $this->bufferHandler->close();
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function addTag($name, $value)
    {
        $this->tags[$name] = $value;
        $this->tagProcessor->addTags(array($name => $value));
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
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
     * @return Logger
     */
    public function create($channelName)
    {
        if(!isset($this->loggers[$channelName])){
            $this->loggers[$channelName] = new Logger($channelName, $this->handlers, $this->processors);
        }

        return $this->loggers[$channelName];
    }

    /**
     * @param $channelName
     * This method should not exist, but for convinience we need to attach extra handler
     * @return Logger
     */
    public function getLogger($channelName)
    {
        if(!isset($this->loggers[$channelName])){
            throw new \LogicException(sprintf('Channel %s not registered', $channelName));
        }

        return $this->loggers[$channelName];
    }
}
