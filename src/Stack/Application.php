<?php

namespace Pyrite\Stack;

use Pyrite\Container\Container;
use Pyrite\OutputBuilder\BinaryOutputBuilder;
use Pyrite\Response\ResponseBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;

class Application implements HttpKernelInterface, TerminableInterface
{
    protected $app;
    protected $container;
    protected $layers = array();
    protected $exceptionHandlers = array();

    public function __construct(Container $container, HttpKernelInterface $app = null, array $layers = array(), array $exceptionHandlers = array())
    {
        $this->app = $app;
        $this->container = $container;
        $this->layers = $layers;
        $this->exceptionHandlers = $exceptionHandlers;
    }

    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        $responseBag = $this->container->get('PyriteResponseBag');

        $stackedLayers = $this->buildLayerStack($request, $this->layers);

        // run them & get the response bag
        if (is_array($stackedLayers) && count($stackedLayers)) {
            try {
                $stackedLayer = reset($stackedLayers);
                $stackedLayer->handle($responseBag);
            } catch (\Exception $e) {
                $handlerFound = false;
                foreach ($this->exceptionHandlers as $exceptionName => $handler) {
                    if ($e instanceof $exceptionName) {
                        $handlerFound = true;
                        $responseBag = call_user_func_array(array($handler, "handleException"), array($e, $responseBag));

                        break;
                    }
                }

                if (!$handlerFound) {
                    throw $e;
                }
            }
        }

        // transform into a Response
        $response = $this->buildResponseFromResponseBag($responseBag);

        return $response;
    }

    protected function buildLayerStack(Request $request, array $layers = array())
    {
        $layerObjects = array();
        foreach ($layers as $layerName => $configuration) {
            $layer = $layerName;
            if (preg_match('/\[\d+\]/', $layerName)) { // allow multiple definitions of a layer type
                $layer = substr($layer, 0, strpos($layer, "["));
            }

            $layerInstance = $this->container->get($layer);

            if (!is_array($configuration)) {
                throw new \Pyrite\Exception\BadConfigurationException(sprintf("Configuration of layer '%s' must be an array, %s given", $layerName, gettype($configuration)));
            }

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
            return $layerObjects;
        } else {
            return array();
        }
    }

    protected function buildResponseFromResponseBag(ResponseBag $responseBag)
    {
        $result = $responseBag->getResult();
        $resultCode = $responseBag->getResultCode();
        $headers = $responseBag->getHeaders();
        $type = $responseBag->getType();

        if ($type === ResponseBag::TYPE_DEFAULT) {
            return new Response($result, $resultCode, $headers);
        }

        if ($type === ResponseBag::TYPE_STREAMED) {
            $callback = $responseBag->getCallback();

            if (null === $callback) {
                throw new \Exception('Streamed response need callback');
            }

            return new StreamedResponse($callback, $resultCode, $headers);
        }

        if ($type === ResponseBag::TYPE_BINARY) {
            $filepath = $responseBag->get(BinaryOutputBuilder::FILEPATH, null);
            $visibility = $responseBag->get(BinaryOutputBuilder::VISIBILITY_PUBLIC, true);
            $autoEtag = $responseBag->get(BinaryOutputBuilder::AUTO_ETAG, false);
            $autoLastModified = $responseBag->get(BinaryOutputBuilder::AUTO_LAST_MODIFIED, true);
            $contentDisposition = $responseBag->get(BinaryOutputBuilder::CONTENT_DISPOSITION, null);

            return new BinaryFileResponse($filepath, $contentDisposition, $visibility, $autoEtag, $autoLastModified);
        }
    }

    /**
     * @param Request  $request
     * @param Response $response
     */
    public function terminate(Request $request, Response $response)
    {

    }
}
