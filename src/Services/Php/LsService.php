<?php

namespace Getevm\Evm\Services\Php;

use Getevm\Evm\Abstracts\Php\LsServiceAbstract;
use Getevm\Evm\Interfaces\LsServiceInterface;
use Getevm\Evm\Services\SystemService;
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
            'Architecture: ' . SystemService::getArchType(),
            'Thread Safe: ' . (ZEND_THREAD_SAFE ? 'Yes' : 'No'),
            'Installation Path: ' . PHP_BINARY
        ]);

        return Command::SUCCESS;
    }
}
