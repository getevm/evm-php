<?php

namespace Getevm\Evm\Commands;

use Getevm\Evm\Services\Php\InstallService;
use Getevm\Evm\Services\Php\UseService;
use Getevm\Evm\Services\SystemService;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PhpCommand extends Command
{
    /**
     * @return void
     */
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

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws GuzzleException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cmd = $input->getArgument('cmd');
        $version = $input->getArgument('version');

        switch ($cmd) {
            case 'install':
                return (new InstallService($this, $input, $output, [
                    'version' => $version,
                    'ts' => $input->getOption('ts'),
                    'archType' => $input->getOption('archType'),
                    'os' => SystemService::toString(),
                    'osType' => $input->getOption('osType'),
                ]))->execute();

            case 'use':
                return (new UseService($output, [
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
