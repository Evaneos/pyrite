<?php

namespace Pyrite\Kernel;

class KernelContext implements KernelContextInterface
{
    /**
     * @var string
     */
    protected $rootDir;

    /**
     * @var string
     */
    protected $cacheDir;

    /**
     * @var string
     */
    protected $logDir;

    /**
     * @var string[]
     */
    protected $configDir;

    /**
     * KernelContext constructor.
     *
     * @param string $cacheDir
     * @param string $logDir
     * @param string[] $configDir
     */
    public function __construct($rootDir, $cacheDir, $logDir, array $configDir = array())
    {
        $this->rootDir = $rootDir;
        $this->cacheDir = $this->rootDir.$cacheDir;
        $this->logDir = $this->rootDir.$logDir;
        $this->configDir = array();

        foreach($configDir as $name => $dir){
            $this->configDir[$name] = $this->rootDir.$dir;
        }
    }

    /**
     * @return string
     */
    public function getRootDir()
    {
        return $this->rootDir;
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return $this->cacheDir;
    }

    /**
     * @return string
     */
    public function getLogDir()
    {
        return $this->logDir;
    }

    /**
     * @return string[]
     */
    public function getConfigDir()
    {
        return $this->configDir;
    }
}
