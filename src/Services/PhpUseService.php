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
        $installationDir = DEPS_PHP_PATH . DIRECTORY_SEPARATOR . $this->buildInstallationDirectoryName();

        if (!is_dir($installationDir)) {
            $this->getOutputInterface()->writeln([
                'This release hasn\'t been installed.'
            ]);

            return Command::FAILURE;
        }

        $this->getOutputInterface()->writeln([
            $installationDir
        ]);

        $oldPaths = array_map(function ($path) {
            return realpath($path);
        }, $this->getPathVariable());

        $newPaths = array_map(function ($path) use ($installationDir) {
            $phpBinaryWithoutExt = str_replace(DIRECTORY_SEPARATOR . pathinfo(PHP_BINARY, PATHINFO_BASENAME), '', PHP_BINARY);

            if ($path !== $phpBinaryWithoutExt) {
                return realpath($path);
            }

            return realpath($installationDir);
        }, $oldPaths);

        $log['oldPaths'] = $oldPaths;
        $log['newPaths'] = $newPaths;

        file_put_contents($this->getPathToDeps() . '/' . time() . '.json', json_encode($log));

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