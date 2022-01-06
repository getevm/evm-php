<?php

namespace Getevm\Evm\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EvmCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('evm')
            ->setDescription('Manage your environment from the terminal')
            ->addArgument('dependency', InputArgument::REQUIRED, 'The dependency to manage')
            ->addArgument('cmd', InputArgument::REQUIRED, 'The command to use');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dependency = $input->getArgument('dependency');
        $output->write($dependency);
        return Command::SUCCESS;
    }
}