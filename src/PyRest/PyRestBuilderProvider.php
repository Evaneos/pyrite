<?php

namespace Pyrite\PyRest;


interface PyRestBuilderProvider
{
    function getBuilder($resourceName);
    function getBuilders();
}