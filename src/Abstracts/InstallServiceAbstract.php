<?php

namespace Getevm\Evm\Abstracts;

use GuzzleHttp\Client;
use Symfony\Component\Console\Output\OutputInterface;

class InstallServiceAbstract
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var array
     */
    private $config;

    /**
     * @var Client
     */
    private $guzzle;

    /**
     * @param OutputInterface $output
     * @param array $config
     * @return void
     */
    public function __construct(OutputInterface $output, array $config)
    {
        $this->output = $output;
        $this->config = $config;
        $this->guzzle = new Client;
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

    /**
     * @return Client
     */
    public function getGuzzle()
    {
        return $this->guzzle;
    }
}