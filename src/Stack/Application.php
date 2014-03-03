<?php

namespace Pyrite\Stack;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\TerminableInterface;

use Pyrite\Response\ResponseBag;

use DICIT\Container;

class Application implements HttpKernelInterface, TerminableInterface
{
    protected $app;
    protected $container;
    protected $layers = array();

    public function __construct(Container $container, HttpKernelInterface $app = null, array $layers = array())
    {
        $this->app = $app;
        $this->container = $container;
        $this->layers = $layers;
    }

    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        $responseBag = $this->container->get('PyriteResponseBag');

        // load layers
        $stackedLayers = $this->buildLayerStack($request, $this->layers);

        // run them & get the response bag
        if(count($stackedLayers)) {
            try {
                $responseBag = $stackedLayers->handle($responseBag);
            }
            catch(\Exception $e) {
                // recupere les exception registered
                // boucle instanceof
                // quand match => appeler callback associé
                // $responseBag = $callback($e, $responseBag);
            }
        }

        // transform into a Response
        $response = $this->buildResponseFromResponseBag($responseBag);

        return $response;
    }

    protected function buildLayerStack(Request $request, array $layers = array()) {
        $layerObjects = array();
        foreach($layers as $layer => $configuration) {
            $layerInstance = $this->container->get($layer);
            $layerInstance->setConfiguration($configuration);
            $layerInstance->setRequest($request);
            $layerObjects[] = $layerInstance;
        }

        $count = count($layerObjects);
        for ($i = 0; $i < $count; $i++) {
            if (array_key_exists($i+1, $layerObjects)) {
                $layerObjects[$i]->setNext($layerObjects[$i+1]);
            }
        }

        if (count($layerObjects)) {
            return reset($layerObjects);
        }
        else {
            return array();
        }
    }

    protected function buildResponseFromResponseBag(ResponseBag $responseBag) {
        $result = $responseBag->getResult();
        $resultCode = $responseBag->getResultCode();

        return new Response($result, $resultCode);
    }

    public function terminate(Request $request, Response $response) {
        exit;
    }
}