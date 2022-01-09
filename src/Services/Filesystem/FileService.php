<?php

namespace Getevm\Evm\Services\Filesystem;

use Getevm\Evm\Services\SystemService;
use Symfony\Component\Process\Process;
use ZipArchive;

class FileService
{
    /**
     * @return string|null
     */
    public static function pathToEvmDir(): ?string
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
        return self::pathToEvmDir();
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
     * @param string $pathToArchive
     * @param string $extractToPath
     * @param bool $deleteAfterExtraction
     * @return bool
     */
    public function unzip(string $pathToArchive, string $extractToPath, bool $deleteAfterExtraction = true)
    {
        $baseDir = pathinfo($pathToArchive, PATHINFO_DIRNAME);
        $ext = pathinfo($pathToArchive, PATHINFO_EXTENSION);

        if (SystemService::getOSType() === 'nt') {
            if ($ext === 'zip') {
                $zip = new ZipArchive();

                if ($zip->open($pathToArchive) !== true) {
                    return false;
                }

                $extracted = $zip->extractTo($extractToPath);
                $zip->close();

                if ($deleteAfterExtraction) {
                    return unlink($pathToArchive);
                }

                return $extracted;
            } else {
                $process = new Process(['tar -xf'], $baseDir);
                $process->run();

                if (!$process->isSuccessful()) {
                    return false;
                }

                if ($deleteAfterExtraction) {
                    return unlink($pathToArchive);
                }

                return true;
            }
        } else {
//            tar -xf
            return ';';
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
     * @return bool
     */
    public function replaceInFile(string $search, string $replace, string $file): bool
    {
        return file_put_contents(
                $file,
                str_replace($search, $replace, file_get_contents($file))
            ) !== false;
    }
}