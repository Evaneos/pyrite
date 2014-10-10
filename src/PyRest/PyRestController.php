<?php

namespace Pyrite\PyRest;

use Pyrite\Response\ResponseBag;
use Pyrite\Layer\AbstractLayer;
use Pyrite\OutputBuilder\OutputBuilder;

use Pyrite\PyRest\Configuration\ResourceNameParser;
use Pyrite\PyRest\Configuration\EmbedParser;
use Pyrite\PyRest\Configuration\PaginationParser;
use Pyrite\PyRest\Configuration\ExpectedResultTypeParser;

use Pyrite\PyRest\Serialization\PaginationDecorator;

use Symfony\Component\HttpFoundation\Request;

class PyRestController extends AbstractLayer
{
    protected $pyRestConfiguration = null;
    protected $container = null;
    protected $serializer = null;

    public function setSerializer($serializer)
    {
        $this->serializer = $serializer;
    }

    public function setPyRestConfiguration(PyRestConfiguration $pyRestConfiguration)
    {
        $this->pyRestConfiguration = $pyRestConfiguration;
    }

    public function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     * @param  ResponseBag $responseBag
     * @return ResponseBag
     */
    public function handle(ResponseBag $responseBag)
    {
        return $this->{$this->config[0]}($responseBag);
    }

    public function nextLayer(ResponseBag $bag)
    {
        $this->aroundNext($bag);
        return $bag->get('data', null);
    }

    protected function getServiceName($resourceName, $serviceName)
    {
        $c = $this->container;
        $definitionNameWithPlaceHolders = $c->getParameter('crud.configuration.definition_name');
        $resolvedServiceName = sprintf($definitionNameWithPlaceHolders, $resourceName, $serviceName);

        return $resolvedServiceName;
    }

    protected function getBertheRESTBuilder($resourceName)
    {
        return $this->container->get($this->getServiceName($resourceName, 'RESTBuilder'));
    }

    public function runRestTransformation(array $result = array())
    {
        $config = $this->pyRestConfiguration;
        $resourceName = $config->getConfig(ResourceNameParser::NAME);
        $embeds = $config->getConfig(EmbedParser::NAME);
        $builder = $this->getBertheRESTBuilder($resourceName);

        list($converted, , ) = $builder->convertAll($result, $embeds);

        return array_values($converted);
    }

    public function getAll(ResponseBag $bag)
    {
        $this->pyRestConfiguration->parseRequest($this->request);
        $bag->set('__PyRestConfiguration', $this->pyRestConfiguration);

        $result = $this->nextLayer($bag);

        $resultRest = $this->runRestTransformation($result);

        $serializer = new PaginationDecorator($this->serializer, $this->pyRestConfiguration, $this->container->get('PyRestUrlGenerator'));
        $output = $serializer->serializeMany($resultRest, array(PaginationDecorator::OPTS_TOTAL => $bag->get('count', 0)));

        $bag->set('data', $output);

        return $bag;
    }

    public function nestedAll(ResponseBag $bag)
    {
        $this->pyRestConfiguration->parseRequest($this->request);
        $bag->set('__PyRestConfiguration', $this->pyRestConfiguration);

        $result = $this->nextLayer($bag);

        $resultRest = $this->runRestTransformation($result);

        $resultType = $this->pyRestConfiguration->getConfig(ExpectedResultTypeParser::NAME);
        if ($resultType === ExpectedResultTypeParser::ONE) {
            $output = $this->serializer->serializeOne(reset($resultRest));
        }
        else {
            $serializer = new PaginationDecorator($this->serializer, $this->pyRestConfiguration, $this->container->get('PyRestUrlGenerator'));
            $output = $serializer->serializeMany($resultRest, array(PaginationDecorator::OPTS_TOTAL => $bag->get('count', 0)));
        }

        $bag->set('data', $output);

        return $bag;
    }

    protected function resourceInspector($resourceName)
    {
        return array(
            'embeds' => array('truc', 'bidule'),
            'filters' => array('id', 'name', 'machin', 'chose'),
            'sorts' => array('id')
        );
    }

    public function optionsGetAll(ResponseBag $bag)
    {
        $this->pyRestConfiguration->parseRequest($this->request);
        $bag->set('data', $this->resourceInspector($this->pyRestConfiguration->getResourceName()));
        return $bag;
    }

    public function get(ResponseBag $bag)
    {
        $this->pyRestConfiguration->parseRequest($this->request);
        $bag->set('__PyRestConfiguration', $this->pyRestConfiguration);

        $result = $this->nextLayer($bag);

        $resultRest = $this->runRestTransformation(array($result));
        $output = $this->serializer->serializeOne(reset($resultRest));

        $bag->set('data', $output);

        return $bag;
    }

    public function put(ResponseBag $bag)
    {
        throw new \RuntimeException('not implemented');
    }

    public function putAll(ResponseBag $bag)
    {
        throw new \RuntimeException('not implemented');
    }

    public function post(ResponseBag $bag)
    {
        throw new \RuntimeException('not implemented');
    }

    public function patch(ResponseBag $bag)
    {
        throw new \RuntimeException('not implemented');
    }

    public function patchAll(ResponseBag $bag)
    {
        throw new \RuntimeException('not implemented');
    }

    public function delete(ResponseBag $bag)
    {
        throw new \RuntimeException('not implemented');
    }

    public function deleteAll(ResponseBag $bag)
    {
        throw new \RuntimeException('not implemented');
    }

    public function options(ResponseBag $bag)
    {
        throw new \RuntimeException('not implemented');
    }
}