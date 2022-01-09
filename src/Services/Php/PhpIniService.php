<?php

namespace Getevm\Evm\Services\Php;

use Getevm\Evm\Services\Filesystem\FileService;

class PhpIniService
{
    /**
     * @var string
     */
    private $pathToInstallationDir;

    /**
     * @var string
     */
    private $pathToIniFile;

    /**
     * @var FileService
     */
    private $fileService;

    public function __construct(string $pathToInstallationDir, FileService $fileService = null)
    {
        $this->pathToInstallationDir = $pathToInstallationDir;
        $this->pathToIniFile = $pathToInstallationDir . DIRECTORY_SEPARATOR . 'php.ini';
        $this->fileService = $fileService ?? new FileService();
    }

    public function enableExtensions(array $extensions)
    {

    }

    /**
     * @return bool
     */
    public function enablePhpIni(): bool
    {
        if (file_exists($this->pathToIniFile)) {
            return true;
        }

        return rename($this->pathToInstallationDir . DIRECTORY_SEPARATOR . 'php.ini-production', $this->pathToIniFile);
    }

    /**
     * @return bool
     */
    public function setExtensionsDir(): bool
    {
        $search = ';extension_dir = "ext"';
        $replace = $this->pathToInstallationDir . DIRECTORY_SEPARATOR . 'ext';
        return $this->fileService->replaceInFile($search, $replace, $this->pathToIniFile);
    }

    /**
     * @param string $pathToCert
     * @return bool
     */
    public function setCurlCAInfo(string $pathToCert): bool
    {
        $search = ';curl.cainfo =';
        $replace = 'curl.cainfo="' . $pathToCert . '"';
        return $this->fileService->replaceInFile($search, $replace, $this->pathToIniFile);
    }

    /**
     * @param string $pathToCert
     * @return bool
     */
    public function setOpenSslCAPath(string $pathToCert): bool
    {
        $search = ';openssl.capath=';
        $replace = 'openssl.capath="' . $pathToCert . '"';
        return $this->fileService->replaceInFile($search, $replace, $this->pathToIniFile);
    }
}