<?php

namespace Getevm\Evm\Commands;

use Getevm\Evm\Services\PhpInstallService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PhpCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('php')
            ->setDescription('Manage your PHP environment')
            ->addArgument('cmd', InputArgument::REQUIRED, 'The command to execute upon the PHP env.')
            ->addArgument('version', InputArgument::OPTIONAL, 'The PHP version')
            ->addOption('nts', null, InputOption::VALUE_NONE, 'Non thread safe?', false)
            ->addOption('archType', null, InputOption::VALUE_REQUIRED, 'Architecture type?', 'x64');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cmd = $input->getArgument('cmd');

        switch ($cmd) {
            case 'install':
                $version = $input->getArgument('version');

                return (new PhpInstallService($output, [
                    'version' => $version,
                    'nts' => $input->getOption('nts'),
                    'archType' => $input->getOption('archType'),
                    'outputPath' => DEPS_PATH
                ]))->execute();

            default:
                return Command::SUCCESS;
        }
    }
}