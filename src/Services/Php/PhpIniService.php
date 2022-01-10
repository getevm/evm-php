<?php

namespace Getevm\Evm\Services\Php;

use Getevm\Evm\Services\Filesystem\FileService;
use Getevm\Evm\Services\SystemService;

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

    /**
     * @param string $pathToInstallationDir
     * @param FileService|null $fileService
     */
    public function __construct(string $pathToInstallationDir, FileService $fileService = null)
    {
        $this->pathToInstallationDir = $pathToInstallationDir;
        $this->pathToIniFile = $pathToInstallationDir . DIRECTORY_SEPARATOR . 'php.ini';
        $this->fileService = $fileService ?? new FileService();
    }

    /**
     * @param array $extensions
     * @return array
     */
    public function enableExtensions(array $extensions): array
    {
        $outcome = [
            'success' => [],
            'failure' => []
        ];

        if (SystemService::getOSType() === 'nt') {
            $iniFile = file_get_contents($this->pathToIniFile);

            foreach ($extensions as $ext) {
                $search = ';extension=' . $ext;
                $replace = 'extension=' . $ext;

                if (strpos($iniFile, $search) !== false) {
                    if ($this->fileService->replaceInFile($search, $replace, $this->pathToIniFile)) {
                        $outcome['success'][] = $ext;
                    } else {
                        $outcome['failure'][] = $ext;
                    }
                } else {
                    $outcome['failure'][] = $ext;
                }
            }
        }

        return $outcome;
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
        if (SystemService::getOSType() === 'nt') {
            $search = ';extension_dir = "ext"';
            $replace = 'extension_dir = "' . $this->pathToInstallationDir . DIRECTORY_SEPARATOR . 'ext"';
            return $this->fileService->replaceInFile($search, $replace, $this->pathToIniFile);
        } else {
            return false;
        }
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