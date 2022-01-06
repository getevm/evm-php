<?php

namespace Getevm\Evm\Services;

use Getevm\Evm\Abstracts\InstallServiceAbstract;
use Getevm\Evm\Interfaces\InstallServiceInterface;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Console\Command\Command;

class PhpInstallService extends InstallServiceAbstract implements InstallServiceInterface
{
    /**
     * @return int
     * @throws GuzzleException
     */
    public function execute()
    {
        $this->createPathToDeps();
        $releaseUrl = $this->getReleaseUrl();
        $ext = pathinfo($releaseUrl, PATHINFO_EXTENSION);
        $outputFileName = $this->buildOutputFileName($ext);

        if (!$releaseUrl) {
            $this->getOutput()->writeln('Failed to get release from OS.');
            return Command::FAILURE;
        }

        $this->getOutput()->writeln('Attempting to download from ' . $releaseUrl . ' (' . SystemService::toString() . ')');

        $response = $this->getGuzzle()->get($releaseUrl);

        if ($response->getStatusCode() < 200 || $response->getStatusCode() > 299) {
            $this->getOutput()->writeln('PHP v' . $this->getConfig()['version'] . ' cannot be found.');
            return Command::INVALID;
        }

        $outputPath = $this->getOutputPath($outputFileName);
        file_put_contents($outputPath . '/' . $outputFileName, $response->getBody());
        $this->getOutput()->writeln('Downloaded to ' . $outputPath);

        $zip = new \ZipArchive();

        $zip->open($outputPath . '/' . $outputFileName);
        $zip->extractTo($outputPath);
        $zip->close();

        unlink($outputPath . '/' . $outputFileName);

        return Command::SUCCESS;
    }

    private function getUnixReleaseUrl()
    {
        return 'https://museum.php.net/php8/php-' . $this->getConfig()['version'] . '.tar.gz';
    }

    private function getWindowsReleaseUrl()
    {
        $url = 'https://windows.php.net/downloads/releases/archives/php-';
        $url .= $this->getConfig()['version'];
        $url .= $this->getConfig()['nts'] ? '-nts' : '';
        $url .= '-Win32-vs16-';
        $url .= $this->getConfig()['archType'];
        $url .= '.zip';

        return $url;
    }

    private function buildOutputFileName($ext)
    {
        $fileName = $this->getConfig()['version'];

        if ($this->getConfig()['nts']) {
            $fileName .= '-nts';
        }

        if (SystemService::getOS() === SystemService::OS_WIN) {
            $fileName .= '-' . $this->getConfig()['archType'];
        }

        $fileName .= '-' . SystemService::toString();
        $fileName .= '.' . $ext;

        return $fileName;
    }

    public function getReleaseUrl()
    {
        if (in_array(SystemService::getOS(), [SystemService::OS_LINUX, SystemService::OS_OSX])) {
            return $this->getUnixReleaseUrl();
        } else if (SystemService::getOS() === SystemService::OS_WIN) {
            return $this->getWindowsReleaseUrl();
        }

        return null;
    }

    private function getOutputPath($outputFileName)
    {
        $outputPath = $this->getPathToDeps() . '/' . pathinfo($outputFileName, PATHINFO_FILENAME);

        if (!is_dir($outputPath)) {
            mkdir($outputPath, null, true);
        }

        return $outputPath;
    }

    private function getPathToDeps()
    {
        $pathToPhpDeps = null;

        switch (SystemService::getOS()) {
            case SystemService::OS_WIN:
                $pathToPhpDeps = $_SERVER['HOMEDRIVE'] . $_SERVER['HOMEPATH'] . '/evm/php';
                break;

            case SystemService::OS_LINUX:
            case SystemService::OS_OSX:
                $pathToPhpDeps = $_SERVER['HOME'] . '/evm/php';
                break;
        }

        return $pathToPhpDeps;
    }

    private function createPathToDeps()
    {
        $pathToPhpDeps = $this->getPathToDeps();

        if ($pathToPhpDeps && !is_dir($pathToPhpDeps)) {
            mkdir($pathToPhpDeps, null, true);
        }
    }
}