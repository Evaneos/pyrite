<?php

namespace Pyrite\Utils;

use Monolog\Formatter\LineFormatter;
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
     */
    public static function attachSentry(LoggerFactory $factory, $callbackUrl, $debug)
    {
        if(true === $debug){
            return;
        }

        $ravenClient = new \Raven_Client($callbackUrl);
        $sentryHandler = new RavenHandler($ravenClient, \Monolog\Logger::ERROR);
        $sentryHandler->setFormatter(new LineFormatter('%message% %context% %extra%\n'));
        $factory->addHandler($sentryHandler);
        $factory->getLogger('app')->pushHandler($sentryHandler);
    }

    /**
     * @param LoggerFactory $factory
     * @param string            $token
     * @param string             $channel
     * @param string              $username
     * @param bool              $debug
     */
    public static function attachSlack(LoggerFactory $factory, $token, $channel, $username, $debug)
    {
        if(true === $debug){
            return;
        }

        $slackHandler = new SlackHandler($token, $channel, $username, true, null, \Monolog\Logger::ERROR, true, false, true);
        $factory->addHandler($slackHandler);
        $factory->getLogger('app')->pushHandler($slackHandler);
    }

    /**
     * @param LoggerFactory $factory
     * @param string              $appName
     * @param bool              $debug
     */
    public static function attachNewRelic(LoggerFactory $factory, $appName, $debug)
    {
        if(true === $debug){
            return;
        }

        $newRelicHandler = new NewRelicHandler(\Monolog\Logger::ERROR, true, $appName, true);
        $factory->addHandler($newRelicHandler);
        $factory->getLogger('app')->pushHandler($newRelicHandler);
    }
}
