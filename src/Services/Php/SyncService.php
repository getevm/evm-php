<?php

namespace Getevm\Evm\Services\Php;

use Getevm\Evm\Abstracts\Php\SyncServiceAbstract;
use Getevm\Evm\Interfaces\SyncServiceInterface;
use Symfony\Component\Console\Command\Command;

class SyncService extends SyncServiceAbstract implements SyncServiceInterface
{
    const PHP_VERSION_DATA_FILE = 'https://getevm.github.io/versions/php.json';

    /**
     * @return int
     */
    public function execute(): int
    {
        $versions = $this->getCurlDownloaderService()->download(self::PHP_VERSION_DATA_FILE);

        if (!$versions) {
            $this->getConsoleOutputService()->error('Failed to download version file.');
            return Command::FAILURE;
        }

        file_put_contents(__DIR__ . '/../../../data/php.json', $versions);

        return Command::SUCCESS;
    }
}
