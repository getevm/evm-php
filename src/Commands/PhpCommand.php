<?php

namespace Getevm\Evm\Commands;

use Getevm\Evm\Services\PhpInstallService;
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
            ->addArgument('cmd', InputArgument::REQUIRED, 'The command to execute upon the PHP env.')
            ->addArgument('version', InputArgument::OPTIONAL, 'The PHP version');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cmd = $input->getArgument('cmd');

        switch ($cmd) {
            case 'install':
                $version = $input->getArgument('version');

                return (new PhpInstallService($output, ['dependency' => 'php', 'version' => $version]))->execute();

            default:
                return Command::SUCCESS;
        }
    }
}