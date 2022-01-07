<?php

namespace Getevm\Evm\Services;

use Getevm\Evm\Abstracts\UseServiceAbstract;
use Getevm\Evm\Interfaces\UseServiceInterface;
use Symfony\Component\Console\Command\Command;

class PhpUseService extends UseServiceAbstract implements UseServiceInterface
{
    public function execute()
    {
        $log = [];
        $this->getOutputInterface()->writeln([
            DEPS_PHP_PATH,
            $this->buildInstallationDirectoryName()
        ]);

//        $paths = array_map(function ($path) use ($outputFolderPath) {
//            $phpBinaryWithoutExt = str_replace(DIRECTORY_SEPARATOR . pathinfo(PHP_BINARY, PATHINFO_BASENAME), '', PHP_BINARY);
//
//            if ($path !== $phpBinaryWithoutExt) {
//                return $path;
//            }
//
//            return $outputFolderPath;
//        }, $this->getPathVariable());
//
//        $log['oldPaths'] = $this->getPathVariable();
//        $log['newPaths'] = $paths;
//
//        file_put_contents($this->getPathToDeps() . '/' . time() . '.json', json_encode($log));

        return Command::SUCCESS;
    }

    private function buildInstallationDirectoryName()
    {
        $folderName = $this->getConfig()['version'];

        if ($this->getConfig()['ts']) {
            $folderName .= '-ts';
        } else {
            $folderName .= '-nts';
        }

        $folderName .= '-'. $this->getConfig()['archType'];
        $folderName .= $this->getConfig()['osType'];

        return $folderName;
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