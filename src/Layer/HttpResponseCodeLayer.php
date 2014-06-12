<?php

namespace Pyrite\Layer;

use Pyrite\Response\ResponseBag;

/**
 * Set a particular HTTP response code (header only), no content will be sent
 */
class HttpResponseCodeLayer extends AbstractLayer implements Layer
{
    const REDIRECTION_BAG_KEY = 'redirection-from-bag-layer';

    public function after(ResponseBag $bag)
    {
        $actionResult    = $bag->get(ResponseBag::ACTION_RESULT, false);
        $hasActionResult = !(false === $actionResult);


        if ($hasActionResult && $this->shouldHandle($actionResult)) {
            $this->setResponseCode($actionResult);
        }
    }

    public function shouldHandle($actionResult)
    {
        return array_key_exists($actionResult, $this->config);
    }

    public function setResponseCode($actionResult)
    {
        $responseCode = $this->config[$actionResult];

        http_response_code($responseCode);
    }

}
