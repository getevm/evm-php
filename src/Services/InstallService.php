<?php

namespace Getevm\Evm\Services;

class InstallService
{
    /**
     * @var string
     */
    private $dependency;
    /**
     * @var string
     */
    private $version;

    /**
     * @param string $dependency
     * @param string $version
     */
    public function __construct($dependency, $version)
    {
        $this->dependency = $dependency;
        $this->version = $version;
    }

    private function install()
    {

    }
}