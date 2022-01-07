<?php

namespace Getevm\Evm\Abstracts;

use GuzzleHttp\Client;
use Symfony\Component\Console\Output\OutputInterface;

class InstallServiceAbstract
{
    /**
     * @var OutputInterface
     */
    private $outputInterface;

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
        $this->outputInterface = $output;
        $this->config = $config;
        $this->guzzle = new Client;
    }

    /**
     * @return OutputInterface
     */
    public function getOutputInterface()
    {
        return $this->outputInterface;
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