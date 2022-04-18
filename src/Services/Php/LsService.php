<?php

namespace Getevm\Evm\Services\Php;

use Getevm\Evm\Abstracts\Php\LsServiceAbstract;
use Getevm\Evm\Interfaces\LsServiceInterface;
use Getevm\Evm\Services\Filesystem\FileService;
use Getevm\Evm\Services\SystemService;
use Symfony\Component\Console\Command\Command;

class LsService extends LsServiceAbstract implements LsServiceInterface
{
    /**
     * @return int
     */
    public function execute(): int
    {
        switch (SystemService::getOS()) {
            case SystemService::OS_WIN:
                $phpBinary = substr(PHP_BINARY, 0, strrpos(PHP_BINARY, '\\'));

                $this->getConsoleOutputService()->std([
                    'PHP: ' . PHP_VERSION,
                    'Thread Safe: ' . (ZEND_THREAD_SAFE ? 'Yes' : 'No'),
                    'Architecture: ' . SystemService::getArchType(),
                    'Installation Path: ' . $phpBinary
                ]);

                $dir = FileService::getPathToInstallationDir();

                foreach (scandir($dir) as $resource) {
                    $path = $dir . DIRECTORY_SEPARATOR . $resource;

                    if (!is_dir($path) || in_array($resource, ['.', '..', 'logs'])) {
                        continue;
                    }

                    list($version, $ts, $arch, $osType) = explode('-', $resource);

                    if ($path === $phpBinary) {
                        $this->getConsoleOutputService()->success($version . ' (active)');
                    } else {
                        $this->getConsoleOutputService()->info($version);
                    }
                }
        }

        return Command::SUCCESS;
    }
}
