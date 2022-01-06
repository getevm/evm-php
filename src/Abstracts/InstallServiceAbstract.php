<?php

namespace Getevm\Evm\Abstracts;

use Symfony\Component\Console\Output\OutputInterface;

class InstallServiceAbstract
{
    private $output;
    private $config;

    /**
     * @param OutputInterface $output
     * @param array $config
     * @return void
     */
    public function __constructor(OutputInterface $output, array $config)
    {
        $this->output = $output;
        $this->config = $config;
    }

    /**
     * @return OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }
}