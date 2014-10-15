<?php

namespace Pyrite\PyRest\Serialization;

use Pyrite\PyRest\PyRestObject;
use Pyrite\PyRest\PyRestUrlGenerator;
use Pyrite\PyRest\PyRestItem;
use Pyrite\PyRest\PyRestProperty;

class EmbedSerializer implements Serializer
{
    protected $urlGenerator;

    public function __construct(PyRestUrlGenerator $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function serializeMany(array $objects = array(), array $options = array())
    {

        $out = array();
        foreach($objects as $object) {
            $serialized = $this->serializeOne($object, $options);
            $out[] = $serialized;
        }
        return $out;
    }

    public function serializeOne(PyRestObject $object, array $options = array())
    {
        $serialized = $object->transform();
        $embeds = $object->getEmbeds();
        foreach($embeds as $embedName => $embed) {
            $serialized[$embedName] = $this->serializeOne($embed);
        }

        if(array_key_exists(Serializer::OPTS_VERBOSITY, $options) && (bool)$options[Serializer::OPTS_VERBOSITY]) {
            $serialized = $this->decorate($object, $serialized);
        }

        return $serialized;
    }

    protected function decorate(PyRestObject $object, $serialized)
    {
        $out = array('data' => $serialized, 'meta' => array());

        if ($object instanceof PyRestObjectPrimitive) {
            return reset($serialized);
        }
        else {
            $out['meta']['resource'] = $object::RESOURCE_NAME;
            $out['meta']['links'] = array();
            $out['meta']['links']['self'] = $this->urlGenerator->getItem($object::RESOURCE_NAME, $object->getId());

            $out['meta']['embeds'] = array();

            foreach($object->getEmbeddables() as $embeddableName => $embedDefinition) {
                $out['meta']['embeds'][$embeddableName] = array();

                if (!($embedDefinition instanceof PyRestProperty)) {
                    $out['meta']['embeds'][$embeddableName]['resource'] = $embedDefinition->getResourceType();
                }

                if ($embedDefinition instanceof PyRestItem) {
                    $out['meta']['embeds'][$embeddableName]['type'] = 'item';
                }
                elseif ($embedDefinition instanceof PyRestProperty) {
                    $out['meta']['embeds'][$embeddableName]['type'] = 'property';
                }
                else {
                    $out['meta']['embeds'][$embeddableName]['type'] = 'collection';
                }

                $out['meta']['embeds'][$embeddableName]['links'] = array();

                if (!($embedDefinition instanceof PyRestProperty)) {
                    $out['meta']['embeds'][$embeddableName]['links']['collection'] = $this->urlGenerator->getCollection($embedDefinition->getResourceType());
                    $out['meta']['embeds'][$embeddableName]['links']['nested'] = $this->urlGenerator->getNested($object::RESOURCE_NAME, $object->getId(), $embeddableName);
                }


                if (($embedDefinition instanceof PyRestItem) || ($embedDefinition instanceof PyRestProperty)) {
                    $out['meta']['embeds'][$embeddableName]['links']['embed'] = $this->urlGenerator->getItem($object::RESOURCE_NAME, $object->getId()) . '?embed=' . $embeddableName;
                }
            }
        }

        return $out;
    }
}