<?php

namespace Pyrite\Utils;

use Monolog\Formatter\LineFormatter;
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
        $sentryHandler = new RavenHandler($ravenClient);
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

        $slackHandler = new SlackHandler($token, $channel, $username, \Monolog\Logger::WARNING);
        $factory->addHandler($slackHandler);
        $factory->getLogger('app')->pushHandler($slackHandler);
    }
}
