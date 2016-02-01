<?php

namespace Pyrite\PyRest;

use Pyrite\Layer\AbstractLayer;
use Pyrite\PyRest\Configuration\EmbedParser;
use Pyrite\PyRest\Configuration\ExpectedResultTypeParser;
use Pyrite\PyRest\Configuration\ResourceNameParser;
use Pyrite\PyRest\Configuration\VerbosityParser;
use Pyrite\PyRest\Serialization\PaginationDecorator;
use Pyrite\PyRest\Serialization\Serializer;
use Pyrite\PyRest\Type\PyRestCollection;
use Pyrite\PyRest\Type\PyRestItem;
use Pyrite\PyRest\Type\PyRestProperty;
use Pyrite\Response\ResponseBag;
use Symfony\Component\HttpFoundation\Request;

class PyRestController extends AbstractLayer
{
    protected $pycfg = null;
    protected $container = null;
    protected $serializer = null;
    protected $builderProvider = null;

    public function setBuilderProvider(PyRestBuilderProvider $object)
    {
        $this->builderProvider = $object;
    }

    public function setSerializer($serializer)
    {
        $this->serializer = $serializer;
    }

    public function setPyRestConfiguration(PyRestConfiguration $pyRestConfiguration)
    {
        $this->pycfg = $pyRestConfiguration;
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

    public function transform(array $result = array())
    {
        $resourceName = $this->pycfg->getConfig(ResourceNameParser::NAME);
        $embeds = $this->pycfg->getConfig(EmbedParser::NAME);

        $builder = $this->builderProvider->getBuilder($resourceName);
        $resultREST = $builder->convertAll($result, $resourceName, $embeds);

        return array_values($resultREST);
    }

    public function getAll(ResponseBag $bag)
    {
        $this->pycfg->parseRequest($this->request);
        $bag->set('__PyRestConfiguration', $this->pycfg);

        $result = $this->nextLayer($bag);

        $resultRest = $this->transform($result);

        $verbose = $this->pycfg->getConfig(VerbosityParser::NAME, VerbosityParser::VERBOSE_YES) == VerbosityParser::VERBOSE_YES ? true : false;
        $serializer = new PaginationDecorator($this->serializer, $this->pycfg, $this->container->get('PyRestUrlGenerator'));
        $output = $serializer->serializeMany($resultRest, array(PaginationDecorator::OPTS_TOTAL => $bag->get('count', 0),
                                                                Serializer::OPTS_VERBOSITY => $verbose));

        $bag->set('data', $output);

        return $bag;
    }

    public function get(ResponseBag $bag)
    {
        $this->pycfg->parseRequest($this->request);
        $bag->set('__PyRestConfiguration', $this->pycfg);

        $result = $this->nextLayer($bag);

        $resultRest = $this->transform(array($result));
        $verbose = $this->pycfg->getConfig(VerbosityParser::NAME, VerbosityParser::VERBOSE_YES) == VerbosityParser::VERBOSE_YES ? true : false;
        $output = $this->serializer->serializeOne(reset($resultRest), array(Serializer::OPTS_VERBOSITY => $verbose));

        $bag->set('data', $output);

        return $bag;
    }


    public function nestedAll(ResponseBag $bag)
    {
        $this->pycfg->parseRequest($this->request);
        $bag->set('__PyRestConfiguration', $this->pycfg);

        $result = $this->nextLayer($bag);

        $resultRest = $this->transform($result);
        $resultType = $this->pycfg->getConfig(ExpectedResultTypeParser::NAME);
        $verbose = $this->pycfg->getConfig(VerbosityParser::NAME, VerbosityParser::VERBOSE_YES) == VerbosityParser::VERBOSE_YES ? true : false;

        if ($resultType === ExpectedResultTypeParser::ONE) {
            $output = $this->serializer->serializeOne(reset($resultRest), array(Serializer::OPTS_VERBOSITY => $verbose));
        } else {
            $serializer = new PaginationDecorator($this->serializer, $this->pycfg, $this->container->get('PyRestUrlGenerator'));
            $output = $serializer->serializeMany($resultRest, array(PaginationDecorator::OPTS_TOTAL => $bag->get('count', 0),
                                                                    Serializer::OPTS_VERBOSITY => $verbose));
        }

        $bag->set('data', $output);

        return $bag;
    }

    public function optionsCollection(ResponseBag $bag)
    {
        $this->pycfg->parseRequest($this->request);
        $resourceName = $this->pycfg->getConfig(ResourceNameParser::NAME);
        $embeds = $this->pycfg->getConfig(EmbedParser::NAME);

        $builder = $this->builderProvider->getBuilder($resourceName);
        $impl = $builder->getRESTFQCNImplementation();
        $embeddables = $impl::getEmbeddables();

        $result = array();
        foreach ($embeddables as $embedName => $typeObject) {
            switch (true) {
                case $typeObject instanceof PyRestProperty :
                    $result[$embedName] = (string)$typeObject;
                    break;
                case $typeObject instanceof PyRestItem :
                case $typeObject instanceof PyRestCollection :
                    $result[$embedName] = array('type' => (string)$typeObject,
                                                'resource' => $typeObject->getResourceType());
                    break;
            }
        }

        $urlGenerator = $this->container->get('PyRestUrlGenerator');
        $self = $urlGenerator->getCollection($resourceName);

        $data = array('meta' => array(  'resource' => $resourceName,
                                        'links' => array("self" => $self),
                                        'embeds' => $result));

        $bag->set('data', $data);

        return $bag;
    }

    public function optionsRoot(ResponseBag $bag)
    {
        $builders = $this->builderProvider->getBuilders();

        $out = array();
        foreach ($builders as $resourceName => $builder) {
            $impl = $builder->getRESTFQCNImplementation();
            $embeddables = $impl::getEmbeddables();

            $result = array();
            foreach ($embeddables as $embedName => $typeObject) {
                switch (true) {
                    case $typeObject instanceof PyRestProperty :
                        $result[$embedName] = (string)$typeObject;
                        break;
                    case $typeObject instanceof PyRestItem :
                    case $typeObject instanceof PyRestCollection :
                        $result[$embedName] = array('type' => (string)$typeObject,
                                                    'resource' => $typeObject->getResourceType());
                        break;
                }
            }

            $urlGenerator = $this->container->get('PyRestUrlGenerator');
            $self = $urlGenerator->getCollection($resourceName);

            $data = array('meta' => array(  'resource' => $resourceName,
                                            'links' => array("self" => $self),
                                            'embeds' => $result));
            $out[$resourceName] = $data;
        }

        $bag->set('data', $out);

        return $bag;
    }

    public function optionsItem(ResponseBag $bag)
    {
        $metaSerializer = $this->container->get('PyRestMetaSerializer');
        $this->pycfg->parseRequest($this->request);
        $bag->set('__PyRestConfiguration', $this->pycfg);

        $result = $this->nextLayer($bag);

        $resultRest = $this->transform(array($result));
        $output = $metaSerializer->serializeOne(reset($resultRest), array());

        $bag->set('data', array("meta" => $output));

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
}
