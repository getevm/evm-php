<?php

namespace Getevm\Evm\Commands;

use Exception;
use Getevm\Evm\Services\Php\InstallService;
use Getevm\Evm\Services\Php\LsService;
use Getevm\Evm\Services\Php\SyncService;
use Getevm\Evm\Services\Php\UseService;
use Getevm\Evm\Services\SystemService;
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
            ->addOption('archType', null, InputOption::VALUE_REQUIRED, 'Get a release targeting an architecture type (x64/x86)', SystemService::getArchType());
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cmd = $input->getArgument('cmd');
        $version = $input->getArgument('version');
        $config = [
            'version' => $version,
            'ts' => $input->getOption('ts'),
            'archType' => $input->getOption('archType'),
            'os' => SystemService::getOSAsString(),
            'osType' => SystemService::getOSType(),
        ];

        switch ($cmd) {
            case 'install':
                return (new InstallService($this, $input, $output, $config))->execute();

            case 'use':
                return (new UseService($output, $config))->execute();

            case 'ls':
                return (new LsService($output, $config))->execute();

            /** Sync /data/php.json with the latest file **/
            case 'sync':
                return (new SyncService($output))->execute();

            default:
                return Command::SUCCESS;
        }
    }
}
