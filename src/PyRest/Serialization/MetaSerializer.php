<?php

namespace Pyrite\PyRest\Serialization;

use Pyrite\PyRest\PyRestObject;
use Pyrite\PyRest\PyRestObjectPrimitive;
use Pyrite\PyRest\PyRestUrlGenerator;
use Pyrite\PyRest\Type\PyRestItem;
use Pyrite\PyRest\Type\PyRestProperty;

class MetaSerializer implements Serializer
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
        $out = array();
        if (!($object instanceof PyRestObjectPrimitive)) {
            $out['resource'] = $object::RESOURCE_NAME;
            $out['links'] = array();

            $out['links']['self'] = $this->urlGenerator->getItem($object::RESOURCE_NAME, $object->getId());

            $out['embeds'] = array();

            foreach($object->getEmbeddables() as $embeddableName => $embedDefinition) {
                $out['embeds'][$embeddableName] = array();

                if (!($embedDefinition instanceof PyRestProperty)) {
                    $out['embeds'][$embeddableName]['resource'] = $embedDefinition->getResourceType();
                }

                if ($embedDefinition instanceof PyRestItem) {
                    $out['embeds'][$embeddableName]['type'] = 'item';
                }
                elseif ($embedDefinition instanceof PyRestProperty) {
                    $out['embeds'][$embeddableName]['type'] = 'property';
                }
                else {
                    $out['embeds'][$embeddableName]['type'] = 'collection';
                }

                $out['embeds'][$embeddableName]['links'] = array();

                if (!($embedDefinition instanceof PyRestProperty)) {
                    $out['embeds'][$embeddableName]['links']['collection'] = $this->urlGenerator->getCollection($embedDefinition->getResourceType());
                    $out['embeds'][$embeddableName]['links']['nested'] = $this->urlGenerator->getNested($object::RESOURCE_NAME, $object->getId(), $embeddableName);
                }


                if (($embedDefinition instanceof PyRestItem) || ($embedDefinition instanceof PyRestProperty)) {
                    $out['embeds'][$embeddableName]['links']['embed'] = $this->urlGenerator->getItem($object::RESOURCE_NAME, $object->getId()) . '?embed=' . $embeddableName;
                }
            }
        }

        return $out;
    }

}