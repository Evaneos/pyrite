<?php

namespace Pyrite\PyRest\Serialization;

use Pyrite\PyRest\PyRestObject;
use Pyrite\PyRest\PyRestUrlGenerator;
use Pyrite\PyRest\PyRestItem;

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
            $serialized = $this->serializeOne($object);
            $out[] = $serialized;
        }
        return $out;
    }

    public function serializeOne(PyRestObject $object)
    {
        $serialized = $object->transform();
        $embeds = $object->getEmbeds();
        foreach($embeds as $embedName => $embed) {
            $serialized[$embedName] = $this->serializeOne($embed);
        }
        $out = array('data' => $serialized, 'meta' => array());

        $out['meta']['resource'] = $object::RESOURCE_NAME;
        $out['meta']['links'] = array();
        $out['meta']['links']['self'] = $this->urlGenerator->getItem($object::RESOURCE_NAME, $object->getId());

        $out['meta']['embeds'] = array();

        foreach($object->getEmbeddables() as $embeddableName => $embedDefinition) {
            $out['meta']['embeds'][$embeddableName] = array();
            $out['meta']['embeds'][$embeddableName]['resource'] = $embedDefinition->getResourceType();

            if ($embedDefinition instanceof PyRestItem) {
                $out['meta']['embeds'][$embeddableName]['type'] = 'item';
            }
            else {
                $out['meta']['embeds'][$embeddableName]['type'] = 'collection';
            }


            $out['meta']['embeds'][$embeddableName]['links'] = array();
            $out['meta']['embeds'][$embeddableName]['links']['collection'] = $this->urlGenerator->getCollection($embedDefinition->getResourceType());
            $out['meta']['embeds'][$embeddableName]['links']['nested'] = $this->urlGenerator->getNested($object::RESOURCE_NAME, $object->getId(), $embeddableName);


            if ($embedDefinition instanceof PyRestItem) {
                $out['meta']['embeds'][$embeddableName]['links']['embed'] = $this->urlGenerator->getItem($object::RESOURCE_NAME, $object->getId()) . '?embed=' . $embeddableName;
            }
        }

        return $out;
    }
}