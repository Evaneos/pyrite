<?php

namespace Pyrite\Kernel;

interface KernelContextInterface
{
    /**
     * @return string
     */
    public function getRootDir();

    /**
     * @return string
     */
    public function getCacheDir();

    /**
     * @return string
     */
    public function getLogDir();

    /**
     * @return string[]
     */
    public function getConfigDir();
}
