<?php

namespace Pyrite\PyRest;

use Pyrite\Response\ResponseBag;
use Pyrite\Layer\AbstractLayer;
use Pyrite\OutputBuilder\OutputBuilder;

use Pyrite\PyRest\Configuration\ResourceNameParser;
use Pyrite\PyRest\Configuration\EmbedParser;
use Pyrite\PyRest\Configuration\PaginationParser;
use Pyrite\PyRest\Configuration\ExpectedResultTypeParser;
use Pyrite\PyRest\Configuration\VerbosityParser;

use Pyrite\PyRest\Serialization\Serializer;
use Pyrite\PyRest\Serialization\PaginationDecorator;

use Symfony\Component\HttpFoundation\Request;

class PyRestController extends AbstractLayer
{
    protected $pyRestConfiguration = null;
    protected $container = null;
    protected $serializer = null;
    protected $converter = null;

    public function setConverter($converter)
    {
        $this->converter = $converter;
    }

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
        $resultREST = $builder->convertAll($result, $resourceName, $embeds);

        return array_values($resultREST);
    }

    public function getAll(ResponseBag $bag)
    {
        $config = $this->pyRestConfiguration;
        $config->parseRequest($this->request);
        $bag->set('__PyRestConfiguration', $config);

        $result = $this->nextLayer($bag);

        $resultRest = $this->runRestTransformation($result);

        $verbose = $config->getConfig(VerbosityParser::NAME, VerbosityParser::VERBOSE_YES) == VerbosityParser::VERBOSE_YES ? true : false;
        $serializer = new PaginationDecorator($this->serializer, $this->pyRestConfiguration, $this->container->get('PyRestUrlGenerator'));
        $output = $serializer->serializeMany($resultRest, array(PaginationDecorator::OPTS_TOTAL => $bag->get('count', 0),
                                                                Serializer::OPTS_VERBOSITY => $verbose));

        $bag->set('data', $output);

        return $bag;
    }

    public function nestedAll(ResponseBag $bag)
    {
        $config = $this->pyRestConfiguration;
        $config->parseRequest($this->request);
        $bag->set('__PyRestConfiguration', $this->pyRestConfiguration);

        $result = $this->nextLayer($bag);

        $resultRest = $this->runRestTransformation($result);
        $resultType = $this->pyRestConfiguration->getConfig(ExpectedResultTypeParser::NAME);
        $verbose = $config->getConfig(VerbosityParser::NAME, VerbosityParser::VERBOSE_YES) == VerbosityParser::VERBOSE_YES ? true : false;

        if ($resultType === ExpectedResultTypeParser::ONE) {
            $output = $this->serializer->serializeOne(reset($resultRest), array(Serializer::OPTS_VERBOSITY => $verbose));
        }
        else {
            $serializer = new PaginationDecorator($this->serializer, $this->pyRestConfiguration, $this->container->get('PyRestUrlGenerator'));
            $output = $serializer->serializeMany($resultRest, array(PaginationDecorator::OPTS_TOTAL => $bag->get('count', 0),
                                                                    Serializer::OPTS_VERBOSITY => $verbose));
        }

        $bag->set('data', $output);

        return $bag;
    }

    protected function resourceInspector($resourceName)
    {
        $config = $this->pyRestConfiguration;
        $resourceName = $config->getConfig(ResourceNameParser::NAME);
        $embeds = $config->getConfig(EmbedParser::NAME);

        $builder = $this->getBertheRESTBuilder($resourceName);
        $impl = $builder->getRESTFQCNImplementation();
        $embeddables = $impl::getEmbeddables();

        $out = array();
        foreach($embeddables as $embedName => $typeObject) {
            switch(true) {
                case $typeObject instanceof \Pyrite\PyRest\PyRestProperty :
                    $out[$embedName] = (string)$typeObject;
                    break;
                case $typeObject instanceof \Pyrite\PyRest\PyRestItem :
                case $typeObject instanceof \Pyrite\PyRest\PyRestCollection :
                    $out[$embedName] = array('type' => (string)$typeObject,
                                             'resource' => $typeObject->getResourceType());
                    break;
            }

        }
        return $out;
    }

    public function optionsGetAll(ResponseBag $bag)
    {
        $this->pyRestConfiguration->parseRequest($this->request);
        $result = $this->resourceInspector($this->pyRestConfiguration->getConfig(ResourceNameParser::NAME));
        $bag->set('data', $result);
        return $bag;
    }

    public function get(ResponseBag $bag)
    {
        $config = $this->pyRestConfiguration;
        $config->parseRequest($this->request);
        $bag->set('__PyRestConfiguration', $this->pyRestConfiguration);

        $result = $this->nextLayer($bag);

        $resultRest = $this->runRestTransformation(array($result));
        $verbose = $config->getConfig(VerbosityParser::NAME, VerbosityParser::VERBOSE_YES) == VerbosityParser::VERBOSE_YES ? true : false;
        $output = $this->serializer->serializeOne(reset($resultRest), array(Serializer::OPTS_VERBOSITY => $verbose));

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