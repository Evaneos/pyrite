<?php

namespace Pyrite\PyRest;

interface PyRestBuilder
{
    public function getRESTFQCNImplementation();
    public function convertAll(array $objects = array(), $resourceName = null, array $embeds = array());
}
