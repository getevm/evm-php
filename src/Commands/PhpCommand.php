<?php

namespace Getevm\Evm\Commands;

use Getevm\Evm\Services\PhpInstallService;
use Getevm\Evm\Services\PhpUseService;
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
        $this
            ->setName('php')
            ->setDescription('Manage your PHP environment')
            ->addArgument('cmd', InputArgument::REQUIRED, 'The command to execute upon the PHP env.')
            ->addArgument('version', InputArgument::OPTIONAL, 'Specify the release version')
            ->addOption('ts', null, InputOption::VALUE_NONE, 'Get a thread safe release (default nts)')
            ->addOption('archType', null, InputOption::VALUE_REQUIRED, 'Get a release targeting an architecture type (x64/x86)', SystemService::getArchType())
            ->addOption('osType', null, InputOption::VALUE_REQUIRED, 'Get a release targeting an OS type (nt/nix)', SystemService::getOSType());
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cmd = $input->getArgument('cmd');
        $version = $input->getArgument('version');

        switch ($cmd) {
            case 'install':
                return (new PhpInstallService($this, $input, $output, [
                    'version' => $version,
                    'ts' => $input->getOption('ts'),
                    'archType' => $input->getOption('archType'),
                    'os' => SystemService::toString(),
                    'osType' => $input->getOption('osType'),
                ]))->execute();

            case 'use':
                return (new PhpUseService($output, [
                    'version' => $version,
                    'ts' => $input->getOption('ts'),
                    'archType' => $input->getOption('archType'),
                    'os' => SystemService::toString(),
                    'osType' => $input->getOption('osType'),
                ]))->execute();

            default:
                return Command::SUCCESS;
        }
    }
}