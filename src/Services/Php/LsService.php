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
        $this->getConsoleOutputService()->std([
            'PHP: ' . PHP_VERSION,
            'Architecture: ' . SystemService::getArchType(),
            'Thread Safe: ' . (ZEND_THREAD_SAFE ? 'Yes' : 'No'),
            'Installation Path: ' . PHP_BINARY
        ]);

        $dir = FileService::getPathToInstallationDir();

        foreach (scandir($dir) as $installation) {
            if (!is_dir($installation) || in_array($installation, ['.', '..', 'logs'])) {
                continue;
            }

            list($version, $ts, $arch, $osType) = explode('-', $installation);

            echo json_encode([
                    $version,
                    $ts,
                    $arch,
                    $osType
                ]) . PHP_EOL;

            echo $dir . DIRECTORY_SEPARATOR . $installation . PHP_EOL;
        }

        return Command::SUCCESS;
    }
}
