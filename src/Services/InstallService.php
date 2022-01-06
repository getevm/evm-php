<?php

namespace Getevm\Evm\Services;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

class InstallService
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
     * @param OutputInterface $output
     * @param array $config
     */
    public function __construct(OutputInterface $output, array $config)
    {
        $this->output = $output;
        $this->config = $config;
    }

    /**
     * @return int
     */
    public function install()
    {
        $this->output->write('Attempting to install PHP v' . $this->config['version'] . '...');

        return Command::SUCCESS;
    }
}