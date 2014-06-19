<?php

namespace Pyrite\Layer;

use Pyrite\Response\ResponseBag;

/**
 * Redirects to a given URL
 */
class RedirectionLayer extends AbstractLayer implements Layer
{

    /**
     * @param  ResponseBag $responseBag
     * @return ResponseBag
     */
    public function handle(ResponseBag $responseBag)
    {
        if (count($this->config) == 1 && array_key_exists(0, $this->config)) {
            $this->redirect($this->config[0]);
        } else {
            $result = $this->aroundNext($responseBag);
            $this->after($responseBag);
        }

        return $responseBag;
    }

    public function after(ResponseBag $bag)
    {
        $actionResult    = $bag->get(ResponseBag::ACTION_RESULT, false);
        $hasActionResult = !(false === $actionResult);

        if ($this->hasRedirection($actionResult)) {
            $this->redirect($this->config[$actionResult]);
        }
    }

    public function hasRedirection($actionResult)
    {
        return array_key_exists($actionResult, $this->config);
    }

    public function redirect($redirectionPath)
    {
        header("Location:" .  $redirectionPath);
    }

}
