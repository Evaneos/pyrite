<?php

namespace Pyrite\Errors;

use Symfony\Component\HttpFoundation\Response;

class ErrorSubscriber
{
    /**
     * @var ErrorSubscription[]
     */
    protected $subscriptions;

    /**
     * ErrorSubscriber constructor.
     *
     * @param ErrorSubscription[] $subscriptions
     */
    public function __construct(array $subscriptions = array())
    {
        $this->subscriptions = $subscriptions;

        // A default handling for all
        if(!array_key_exists('\Exception', $subscriptions)){
            $this->subscriptions['\Exception'] = new ErrorSubscription('http-error', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @return array|ErrorSubscription[]
     */
    public function getSubscribedError()
    {
        return $this->subscriptions;
    }
}
