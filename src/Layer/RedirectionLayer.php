<?php

namespace Pyrite\Layer;

use Pyrite\Response\ResponseBag;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Redirects to a given URL
 */
class RedirectionLayer extends AbstractLayer implements Layer
{
    const MAGIC_KEY_ROUTE_REFERENCE = '@';

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
            if ($this->config[0][0] === self::MAGIC_KEY_ROUTE_REFERENCE) {
                $url = $this->urlGenerator->generate(substr($this->config[0], 1));
                $this->redirect($url, $responseBag);
            } else {
                $this->redirect($this->config[0], $responseBag);
            }
        } else {
            $result = $this->aroundNext($responseBag);
            $this->after($responseBag);
        }

        return $responseBag;
    }

    public function after(ResponseBag $responseBag)
    {
        $actionResult    = $responseBag->get(ResponseBag::ACTION_RESULT, false);
        $hasActionResult = !(false === $actionResult);

        if ($hasActionResult && $this->hasRedirection($actionResult)) {
            if ($actionResult[0] === self::MAGIC_KEY_ROUTE_REFERENCE) {
                $url = $this->urlGenerator->generate(substr($actionResult, 1));
                $this->redirect($url, $responseBag);
            } else {
                $this->redirect($this->config[$actionResult], $responseBag);
            }
        }

        return $responseBag;
    }

    public function hasRedirection($actionResult)
    {
        return array_key_exists($actionResult, $this->config);
    }

    public function redirect($redirectionPath, ResponseBag $responseBag)
    {
        $responseBag->addHeader('Location', $redirectionPath);
    }
}
