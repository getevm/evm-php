<?php

namespace Getevm\Evm\Abstracts;

use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallServiceAbstract
{
    /**
     * @var Command
     */
    private $command;

    private $inputInterface;

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
     * @param Command $command
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param array $config
     */
    public function __construct(Command $command, InputInterface $input, OutputInterface $output, array $config)
    {
        $this->command = $command;
        $this->inputInterface = $input;
        $this->outputInterface = $output;
        $this->config = $config;
        $this->guzzle = new Client;
    }

    /**
     * @return Command
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @return InputInterface
     */
    public function getInputInterface()
    {
        return $this->inputInterface;
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