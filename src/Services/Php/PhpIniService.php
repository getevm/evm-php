<?php

namespace Getevm\Evm\Services\Php;

use Getevm\Evm\Services\Filesystem\FileService;

class PhpIniService
{
    /**
     * @var string
     */
    private $pathToIniFile;

    /**
     * @var FileService
     */
    private $fileService;

    public function __construct(string $pathToIniFile)
    {
        $this->pathToIniFile = $pathToIniFile;
        $this->fileService = new FileService();
    }

    public function enableExtensions(array $extensions)
    {

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