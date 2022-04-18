<?php

namespace Getevm\Evm\Services\Php;

use Getevm\Evm\Abstracts\Php\SyncServiceAbstract;
use Getevm\Evm\Interfaces\SyncServiceInterface;
use Symfony\Component\Console\Command\Command;

class SyncService extends SyncServiceAbstract implements SyncServiceInterface
{
    /**
     * @return int
     */
    public function execute(): int
    {
        return Command::SUCCESS;
    }
}
