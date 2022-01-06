<?php

namespace Getevm\Evm\Services;

use Getevm\Evm\Interfaces\InstallServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

class PhpInstallService implements InstallServiceInterface
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
     * @return int
     */
    public function execute()
    {
        $this->output->writeln('Requesting PHP v' . $this->config['version']);

        return Command::SUCCESS;
    }
}