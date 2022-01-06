<?php

namespace Getevm\Evm\Services;

use Getevm\Evm\Abstracts\InstallServiceAbstract;
use Getevm\Evm\Interfaces\InstallServiceInterface;
use Symfony\Component\Console\Command\Command;

class PhpInstallService extends InstallServiceAbstract implements InstallServiceInterface
{
    /**
     * @return int
     */
    public function execute(): int
    {
        $this->getOutput()->writeln('Requesting PHP v' . $this->getConfig()['version']);

        return Command::SUCCESS;
    }
}