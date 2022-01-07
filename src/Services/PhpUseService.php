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
        $installationDirPath = DEPS_PHP_PATH . DIRECTORY_SEPARATOR . $installationDirName;

        if (!is_dir($installationDirPath)) {
            $this->getOutputInterface()->writeln([
                'This release hasn\'t been installed.'
            ]);

            return Command::FAILURE;
        }

        $this->getOutputInterface()->writeln([
            $installationDirPath
        ]);

        $oldPaths = array_map(function ($path) {
            return realpath($path);
        }, $this->getPathVariable());

        $newPaths = array_map(function ($path) use ($installationDirPath) {
            $phpBinaryWithoutExt = str_replace(DIRECTORY_SEPARATOR . pathinfo(PHP_BINARY, PATHINFO_BASENAME), '', PHP_BINARY);

            if ($path !== $phpBinaryWithoutExt) {
                return realpath($path);
            }

            return realpath($installationDirPath);
        }, $oldPaths);

        $logs['oldPaths'] = $oldPaths;
        $logs['newPaths'] = $newPaths;

        $pathToLogs = OSHelper::getPathToDeps() . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'logs';
        $fileName = date('YmdHis') . '_' . $installationDirName . '.json';

        $pathToBatchFile = '"' . __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'setpath.bat' . '"';

        $this->getOutputInterface()->writeln([
            $pathToBatchFile,
            implode(';', $newPaths),
            ('cmd /c ' . ($pathToBatchFile) . ' "' . (implode(';', $newPaths)) . '"')
        ]);

//        exec(
//            'cmd /c ' . $pathToBatchFile . ' "' . implode(';', $newPaths) . '"',
//            $output
//        );

//        $logs['output'] = $output;

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