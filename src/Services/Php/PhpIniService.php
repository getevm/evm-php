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

    public function setCurlCAInfo(string $pathToCert)
    {
        $this->fileService->replaceInFile(';curl.cainfo =', $pathToCert, $this->pathToIniFile);
    }

    public function setOpenSslCAPath(string $pathToCert)
    {
        $this->fileService->replaceInFile(';openssl.capath=', $pathToCert, $this->pathToIniFile);
    }
}