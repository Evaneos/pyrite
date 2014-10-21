<?php

namespace Pyrite\PyRest\Serialization;

use Pyrite\PyRest\PyRestObject;


interface Serializer
{
    const OPTS_VERBOSITY = 'verbosity';

    const VERBOSE_YES = 1;
    const VERBOSE_NO  = 0;

    function serializeMany(array $objects = array(), array $options = array());
    function serializeOne(PyRestObject $object, array $options = array());
}