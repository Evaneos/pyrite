<?php

namespace Pyrite\PyRest;

interface PyRestBuilderProvider
{
    public function getBuilder($resourceName);
    public function getBuilders();
}
