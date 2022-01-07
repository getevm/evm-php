<?php

namespace Getevm\Evm\Commands;

use Getevm\Evm\Services\PhpInstallService;
use Getevm\Evm\Services\SystemService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

//use Getevm\Evm\Services\PhpUseService;

class PhpCommand extends Command
{
    protected function configure()
    {
        $threadSafety = SystemService::getOSType() === 'nt';

        $this
            ->setName('php')
            ->setDescription('Manage your PHP environment')
            ->addArgument('cmd', InputArgument::REQUIRED, 'The command to execute upon the PHP env.')
            ->addArgument('version', InputArgument::OPTIONAL, 'The PHP version')
            ->addOption('ts', null, InputOption::VALUE_NONE, 'Non thread safe?', $threadSafety)
            ->addOption('archType', null, InputOption::VALUE_REQUIRED, 'Architecture type?', SystemService::getArchType())
            ->addOption('os', null, InputOption::VALUE_REQUIRED, 'Get release for specific OS', SystemService::getOSType());
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cmd = $input->getArgument('cmd');

        switch ($cmd) {
            case 'install':
                $version = $input->getArgument('version');

                return (new PhpInstallService($output, [
                    'version' => $version,
                    'ts' => $input->getOption('ts'),
                    'archType' => $input->getOption('archType'),
                    'os' => SystemService::toString(),
                    'osType' => $input->getOption('osType'),
                ]))->execute();

//            case 'use':
//                $version = $input->getOption('version');
//
//                return (new PhpUseService($output, [
//                    'version' => $version,
//                    'nts' => $input->getOption('nts'),
//                    'archType' => $input->getOption('archType')
//                ]))->execute();

            default:
                return Command::SUCCESS;
        }
    }
}