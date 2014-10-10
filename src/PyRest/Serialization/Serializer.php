<?php

namespace Pyrite\PyRest\Serialization;

use Pyrite\PyRest\PyRestObject;


interface Serializer
{
    function serializeMany(array $objects = array(), array $options = array());
    function serializeOne(PyRestObject $object);
}