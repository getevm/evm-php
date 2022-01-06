<?php

namespace Getevm\Evm\Services;

use Getevm\Evm\Abstracts\InstallServiceAbstract;
use Getevm\Evm\Interfaces\InstallServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

class PhpInstallService extends InstallServiceAbstract implements InstallServiceInterface
{
    public function __constructor(OutputInterface $output, array $config)
    {
        parent::__constructor($output, $config);
    }

    /**
     * @return int
     */
    public function execute()
    {
        $this->getOutput()->writeln('Requesting PHP v' . $this->getConfig()['version']);

        return Command::SUCCESS;
    }
}