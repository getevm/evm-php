<?php

namespace Getevm\Evm\Services;

use Getevm\Evm\Abstracts\UseServiceAbstract;
use Getevm\Evm\Interfaces\UseServiceInterface;
use Symfony\Component\Console\Command\Command;

class PhpUseService extends UseServiceAbstract implements UseServiceInterface
{
    public function execute()
    {
        $logs = [];
        $installationDirName = $this->buildInstallationDirectoryName();
        $oldInstallationDirPath = null;
        $newInstallationDirPath = DEPS_PATH . DIRECTORY_SEPARATOR . $installationDirName;

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

        $pathToLogs = OSHelper::getPathToDeps() . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'logs';
        $fileName = date('YmdHis') . '_' . $installationDirName . '.json';

        $pathToBatchFile = '"' . __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'setpath.bat' . '"';

        exec($pathToBatchFile . ' "' . $oldInstallationDirPath . '" "' . $newInstallationDirPath . '" 2>&1', $output);
        $logs['output'] = $output;
        $this->getOutputInterface()->writeln($output);
        file_put_contents($pathToLogs . DIRECTORY_SEPARATOR . $fileName, json_encode($logs, JSON_PRETTY_PRINT));

        return Command::SUCCESS;
    }

    private function buildInstallationDirectoryName()
    {
        $dir = $this->getConfig()['version'];
        $dir .= $this->getConfig()['ts'] ? '-ts' : '-nts';
        $dir .= '-' . $this->getConfig()['archType'];
        $dir .= '-' . $this->getConfig()['osType'];

        return $dir;
    }

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