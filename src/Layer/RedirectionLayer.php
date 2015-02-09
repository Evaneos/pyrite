<?php

namespace Pyrite\Layer;

use Pyrite\Response\ResponseBag;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Redirects to a given URL
 */
class RedirectionLayer extends AbstractLayer implements Layer
{

    /**
     * @var UrlGeneratorInterface urlGenerator
     */
    protected $urlGenerator;


    public function setUrlGenerator(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param  ResponseBag $responseBag
     * @return ResponseBag
     */
    public function handle(ResponseBag $responseBag)
    {
        if (count($this->config) == 1 && array_key_exists(0, $this->config)) {
            if ($this->config[0][0] == '@') {
                $url = $this->urlGenerator->generate(substr($this->config[0], 1));
                $this->redirect($url);
            } else {
                $this->redirect($this->config[0]);
            }

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
            if ($actionResult[0] == '@') {
                $url = $this->urlGenerator->generate(substr($actionResult, 1));
                $this->redirect($url);
            } else {
                $this->redirect($this->config[$actionResult]);
            }
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
