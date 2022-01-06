<?php

namespace Getevm\Evm\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EvmCommand extends Command
{
    private $dependencies = ['php', 'mysql'];

    protected function configure()
    {
        foreach ($this->dependencies as $dependency) {
            $this
                ->setName($dependency)
                ->setDescription('Manage your ' . $dependency . ' environment');
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write('success!');
        return Command::SUCCESS;
    }
}