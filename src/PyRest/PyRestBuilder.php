<?php

namespace Pyrite\PyRest;


interface PyRestBuilder
{
    function getRESTFQCNImplementation();
    function convertAll(array $objects = array(), $resourceName = null, array $embeds = array());
}