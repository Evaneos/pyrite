<?php

namespace Pyrite\Config;

use DICIT\Config\AbstractConfig;

class NullConfig extends AbstractConfig
{
    /**
     * @inheritdoc
     */
    protected function doLoad()
    {
        return [];
    }
}
