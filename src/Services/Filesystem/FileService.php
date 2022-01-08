<?php

namespace Getevm\Evm\Services\Filesystem;

use Getevm\Evm\Services\SystemService;

class FileService
{
    public function pathToEvmDir()
    {
        switch (SystemService::getOS()) {
            case SystemService::OS_WIN:
                $path = $_SERVER['HOMEDRIVE'] . DIRECTORY_SEPARATOR . 'evm';
                break;
            case SystemService::OS_LINUX:
            case SystemService::OS_OSX:
                $path = $_SERVER['HOME'] . DIRECTORY_SEPARATOR . 'evm';
                break;
        }

        if (!isset($path)) {
            return null;
        }

        if (!is_dir($path)) {
            mkdir($path, null, true);

            return $path;
        }

        return $path;
    }

    /**
     * @return string|null
     */
    public static function getPathToInstallationDir(): ?string
    {
        return self::getPathToInstallationDir();
    }

    /**
     * @return string
     */
    public static function getPathToLogsDir(): string
    {
        return self::getPathToInstallationDir() . DIRECTORY_SEPARATOR . 'logs';
    }

    /**
     * Create the prerequisite dirs e.g. logs
     * @return void
     */
    public function createPrerequisiteDirectories()
    {
        $dirs = [
            self::getPathToLogsDir()
        ];

        foreach ($dirs as $dir) {
            if (is_dir($dir)) {
                continue;
            }

            mkdir($dir, null, true);
        }
    }

    /**
     * @param string $path
     * @return mixed
     */
    public function getAsJson(string $path)
    {
        return json_decode(file_get_contents($path), true);
    }

    /**
     * @param string $search
     * @param string $replace
     * @param string $file
     * @return void
     */
    public function replaceInFile(string $search, string $replace, string $file)
    {
        file_put_contents(
            $file,
            str_replace($search, $replace, file_get_contents($file))
        );
    }
}