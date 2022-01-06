<?php

namespace Getevm\Evm\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PhpCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('php')
            ->setDescription('Manage your PHP environment')
            ->addArgument('cmd', InputArgument::REQUIRED, 'The command to execute upon the PHP env.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write('success!');
        return Command::SUCCESS;
    }
}