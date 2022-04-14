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

        $this->getOutputInterface()->writeln($this->getPathVariable());

        exit;

        if (!is_dir($newInstallationDirPath)) {
            $this->getOutputInterface()->writeln([
                'This release hasn\'t been installed.'
            ]);

            return Command::FAILURE;
        }

        $oldPaths = array_map(function ($path) {
            return realpath($path);
        }, $this->getPathVariable());

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

        $pathToBatchFile = '"' . __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'setpath.bat' . '"';

        exec($pathToBatchFile . ' "' . $oldInstallationDirPath . '" "' . $newInstallationDirPath . '" 2>&1', $output);
        $logs['output'] = $output;
        $this->getOutputInterface()->writeln($output);
        file_put_contents(FileService::getPathToLogsDir() . DIRECTORY_SEPARATOR . $fileName, json_encode($logs, JSON_PRETTY_PRINT));

        return Command::SUCCESS;
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
     * @return false|string[]|void
     */
    private function getPathVariable()
    {
        switch (SystemService::getOS()) {
            case SystemService::OS_WIN:
                exec('echo %Path%', $output);
                return array_filter(explode(';', $output[0]), function ($v) {
                    return !empty($v);
                });

            case SystemService::OS_LINUX:
            case SystemService::OS_OSX:
                exec('echo $PATH', $output);
                return array_filter(explode(':', $output[0]), function ($v) {
                    return !empty($v);
                });
        }
    }
}
