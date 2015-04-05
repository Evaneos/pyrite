<?php

namespace Pyrite\Config;

use DICIT\Config\AbstractConfig;

class NullConfig extends AbstractConfig
{
    protected function doLoad()
    {
        return array();
    }
}
