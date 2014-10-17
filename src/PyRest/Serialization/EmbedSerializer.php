<?php

namespace Pyrite\PyRest\Serialization;

use Pyrite\PyRest\PyRestObject;
use Pyrite\PyRest\PyRestUrlGenerator;
use Pyrite\PyRest\Type\PyRestItem;
use Pyrite\PyRest\Type\PyRestProperty;

class EmbedSerializer implements Serializer
{
    protected $urlGenerator;
    protected $metaSerializer;

    public function __construct(PyRestUrlGenerator $urlGenerator, MetaSerializer $metaSerializer)
    {
        $this->urlGenerator = $urlGenerator;
        $this->metaSerializer = $metaSerializer;
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
            $serialized[$embedName] = $this->serializeOne($embed, $options);
        }

        if(array_key_exists(Serializer::OPTS_VERBOSITY, $options) && (bool)$options[Serializer::OPTS_VERBOSITY]) {
            $meta = $this->metaSerializer->serializeOne($object, $options);
            $serialized = array('data' => $serialized, 'meta' => $meta);
        }
        else {
            $serialized = array('data' => $serialized);
        }

        return $serialized;
    }
}