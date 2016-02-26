<?php

namespace Pyrite\Layer;

use Pyrite\Response\ResponseBag;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirects to a given URL found in the bag
 */
class RedirectionFromBagLayer extends AbstractLayer implements Layer
{
    const REDIRECTION_BAG_KEY = 'redirection-from-bag-layer';

    public function after(ResponseBag $bag)
    {
        $actionResult    = $bag->get(ResponseBag::ACTION_RESULT, false);
        $hasActionResult = !(false === $actionResult);

        if ($hasActionResult && $this->hasRedirection($actionResult)) {
            $this->redirect($actionResult, $bag);
        }
    }

    public function hasRedirection($actionResult)
    {
        return array_key_exists($actionResult, $this->config);
    }

    /**
     * @param             $actionResult
     * @param ResponseBag $bag
     */
    public function redirect($actionResult, ResponseBag $bag)
    {
        $redirectionPath = $bag->get(RedirectionFromBagLayer::REDIRECTION_BAG_KEY);
        $bag->setResultCode(Response::HTTP_FOUND);
        $bag->addHeader('Location',$redirectionPath);
    }
}
