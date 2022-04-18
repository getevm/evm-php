<?php

namespace Getevm\Evm\Services\Php;

use Getevm\Evm\Abstracts\Php\UseServiceAbstract;
use Getevm\Evm\Interfaces\UseServiceInterface;
use Getevm\Evm\Services\Filesystem\FileService;
use Getevm\Evm\Services\SystemService;
use Symfony\Component\Console\Command\Command;

class UseService extends UseServiceAbstract implements UseServiceInterface
{
    /**
     * @return int
     */
    public function execute(): int
    {
        $logs = [];
        $installationDirName = $this->buildInstallationDirectoryName();
        $oldInstallationDirPath = null;
        $newInstallationDirPath = FileService::getPathToInstallationDir() . DIRECTORY_SEPARATOR . $installationDirName;

        if (!is_dir($newInstallationDirPath)) {
            $this->getConsoleOutputService()->error('This release hasn\'t been installed.');
            return Command::FAILURE;
        }

        $paths = $this->getPathVariablesAsArray();

        if (!$paths) {
            $this->getConsoleOutputService()->error('Unable to get system PATH variables.');
            return Command::FAILURE;
        }

        $oldPaths = array_map(function ($path) {
            return realpath($path);
        }, $paths);

        $newPaths = array_map(function ($path) use ($newInstallationDirPath, &$oldInstallationDirPath) {
            $phpBinaryWithoutExt = str_replace(DIRECTORY_SEPARATOR . pathinfo(PHP_BINARY, PATHINFO_BASENAME), '', PHP_BINARY);

            if ($path !== $phpBinaryWithoutExt) {
                return realpath($path);
            }

            $oldInstallationDirPath = $path;

            return realpath($newInstallationDirPath);
        }, $oldPaths);

        $logs['oldPaths'] = $oldPaths;
        $logs['newPaths'] = $newPaths;

        $fileName = date('YmdHis') . '_' . $installationDirName . '.json';
        $pathToLogFile = FileService::getPathToLogsDir() . DIRECTORY_SEPARATOR . $fileName;

        switch (SystemService::getOS()) {
            case SystemService::OS_WIN:
                if (strlen(implode(';', $newPaths)) > 1024) {
                    $this->getConsoleOutputService()->error([
                        'Unable to set the path variable as the character limit has been reached. This is a restriction on Windows.',
                        'You\'ll need to set the path manually: ' . $newInstallationDirPath
                    ]);
                    return Command::FAILURE;
                }

                $pathToBatchFile = '"' . __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'setpath.bat' . '"';
                $exec = exec($pathToBatchFile . ' "' . $oldInstallationDirPath . '" "' . $newInstallationDirPath . '" 2>&1', $output);

                if ($exec === false) {
                    $this->getConsoleOutputService()->error([
                        'Failed to activate release.',
                        'You\'ll need to set the path manually: ' . $newInstallationDirPath
                    ]);
                    return Command::FAILURE;
                }

                $output = implode('', array_values(array_filter($output, 'strlen')));
                $logs['output'] = $output;

                file_put_contents($pathToLogFile, json_encode($logs, JSON_PRETTY_PRINT));

                if (strpos($output, 'SUCCESS') === false) {
                    $this->getConsoleOutputService()->error($output);
                    return Command::FAILURE;
                }

                $this->getConsoleOutputService()->success('Release has been activated. Refresh any terminals before attempting to use.');

                return Command::SUCCESS;

            case SystemService::OS_LINUX:
            case SystemService::OS_OSX:
                return Command::SUCCESS;

            default:
                return Command::FAILURE;
        }
    }

    /**
     * @return string
     */
    private function buildInstallationDirectoryName(): string
    {
        $dir = $this->getConfig()['version'];
        $dir .= $this->getConfig()['ts'] ? '-ts' : '-nts';
        $dir .= '-' . $this->getConfig()['archType'];
        $dir .= '-' . $this->getConfig()['osType'];

        return $dir;
    }

    /**
     * @return string|null
     */
    private function getPathVariables(): ?string
    {
        switch (SystemService::getOS()) {
            case SystemService::OS_WIN:
                $exec = exec('echo %Path%', $output);

                if ($exec === false) {
                    return null;
                }

                return array_values(array_filter($output, 'strlen'))[0] ?? null;

            case SystemService::OS_LINUX:
            case SystemService::OS_OSX:
                $exec = exec('echo $PATH', $output);

                if ($exec === false) {
                    return null;
                }

                return array_values(array_filter($output, 'strlen'))[0] ?? null;

            default:
                return null;
        }
    }

    /**
     * @return array
     */
    private function getPathVariablesAsArray(): array
    {
        $path = $this->getPathVariables();

        if (!$path) {
            return [];
        }

        $separators = [
            SystemService::OS_WIN => ';',
            SystemService::OS_LINUX => ':',
            SystemService::OS_OSX => ':'
        ];
        $separator = $separators[SystemService::getOS()] ?? null;

        if (!$separator) {
            return [];
        }

        return array_filter(explode($separator, $path), function ($v) {
            return !empty($v);
        });
    }
}
