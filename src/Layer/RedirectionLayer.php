<?php

namespace Pyrite\Layer;

use Pyrite\Response\ResponseBag;

/**
 * Redirects to a given URL
 */
class RedirectionLayer extends AbstractLayer implements Layer
{
    public function after(ResponseBag $bag)
    {
        $actionResult    = $bag->get(ResponseBag::ACTION_RESULT, false);
        $hasActionResult = !(false === $actionResult);

        if ($this->hasRedirection($actionResult)) {
            $this->redirect($actionResult);
        }
    }

    public function hasRedirection($actionResult)
    {
        return array_key_exists($actionResult, $this->config);
    }

    public function redirect($actionResult)
    {
        $redirectionPath = $this->config[$actionResult];
        header("Location:" .  $redirectionPath);
    }

}
