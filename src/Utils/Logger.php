<?php

namespace Pyrite\Utils;

use Monolog\Formatter\LineFormatter;
use Monolog\Logger as MonologLogger;
use Monolog\Handler\NewRelicHandler;
use Monolog\Handler\RavenHandler;
use Monolog\Handler\SlackHandler;
use Pyrite\Logger\LoggerFactory;

class Logger
{
    /**
     * @param LoggerFactory $factory
     * @param string        $callbackUrl
     * @param bool          $debug
     * @param int           $level The minimum logging level at which this handler will be triggered
     */
    public static function attachSentry(LoggerFactory $factory, $callbackUrl, $debug, $level = MonologLogger::ERROR)
    {
        if(true === $debug){
            return;
        }

        $ravenClient = new \Raven_Client($callbackUrl);
        $sentryHandler = new RavenHandler($ravenClient, $level);
        $sentryHandler->setFormatter(new LineFormatter('%message% %context% %extra%\n'));
        $factory->addHandler($sentryHandler);
        $factory->getLogger('app')->pushHandler($sentryHandler);
    }

    /**
     * @param LoggerFactory $factory
     * @param string        $token
     * @param string        $channel
     * @param string        $username
     * @param bool          $debug
     * @param int           $level The minimum logging level at which this handler will be triggered
     */
    public static function attachSlack(LoggerFactory $factory, $token, $channel, $username, $debug, $level = MonologLogger::ERROR)
    {
        if(true === $debug){
            return;
        }

        $slackHandler = new SlackHandler($token, $channel, $username, true, null, $level, true, false, true);
        $factory->addHandler($slackHandler);
        $factory->getLogger('app')->pushHandler($slackHandler);
    }

    /**
     * @param LoggerFactory $factory
     * @param string        $appName
     * @param bool          $debug
     * @param int           $level The minimum logging level at which this handler will be triggered
     */
    public static function attachNewRelic(LoggerFactory $factory, $appName, $debug, $level = MonologLogger::ERROR)
    {
        if(true === $debug || !extension_loaded('newrelic')){
            return;
        }

        $newRelicHandler = new NewRelicHandler($level, true, $appName, true);
        $factory->addHandler($newRelicHandler);
        $factory->getLogger('app')->pushHandler($newRelicHandler);
    }
}
