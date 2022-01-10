<?php

namespace Getevm\Evm\Services\Php;

use Getevm\Evm\Abstracts\LsServiceAbstract;
use Getevm\Evm\Interfaces\LsServiceInterface;
use Symfony\Component\Console\Command\Command;

class LsService extends LsServiceAbstract implements LsServiceInterface
{
    /**
     * @return int
     */
    public function execute(): int
    {
        $this->getConsoleOutputService()->success([
            'PHP: ' . PHP_VERSION,
            'Installation Directory: ' . PHP_BINARY
        ]);

        return Command::SUCCESS;
    }
}