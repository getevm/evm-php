<?php

namespace Getevm\Evm\Services\Php;

use Getevm\Evm\Abstracts\Php\SyncServiceAbstract;
use Getevm\Evm\Interfaces\SyncServiceInterface;
use Symfony\Component\Console\Command\Command;

class SyncService extends SyncServiceAbstract implements SyncServiceInterface
{
    const PHP_VERSION_DATA_FILE = 'https://getevm.github.io/versions/php.json';
    const PATH_TO_LOCAL_VERSION_FILE = __DIR__ . '/../../../data/php.json';

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

        if ($versions === file_get_contents(SyncService::PATH_TO_LOCAL_VERSION_FILE)) {
            $this->getConsoleOutputService()->success('No changes detected to synchronise.');
            return Command::SUCCESS;
        }

        $pathToVersionFile = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR;

        if (!is_dir($pathToVersionFile)) {
            mkdir($pathToVersionFile, null, true);
        }

        $pathToVersionFile .= 'php.json';

        if (file_put_contents($pathToVersionFile, $versions) === false) {
            $this->getConsoleOutputService()->error('Failed to write synchronised version file.');
            return Command::FAILURE;
        }

        $this->getConsoleOutputService()->success('Successfully synchronised version file.');

        return Command::SUCCESS;
    }
}
