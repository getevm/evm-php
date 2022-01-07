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
        $this->createRootInstallationDirectory();

        $releaseUrl = $this->getReleaseUrl();
        $ext = pathinfo($releaseUrl, PATHINFO_EXTENSION);
        $outputZipFile = $this->buildOutputFileName($ext);

        if (!$releaseUrl) {
            $this->getOutputInterface()->writeln('Failed to get release from OS.');
            return Command::FAILURE;
        }

        $this->getOutputInterface()->writeln('Attempting to download from ' . $releaseUrl . ' (' . SystemService::toString() . ')');

        $response = $this->getGuzzle()->get($releaseUrl);

        if ($response->getStatusCode() < 200 || $response->getStatusCode() > 299) {
            $this->getOutputInterface()->writeln('PHP v' . $this->getConfig()['version'] . ' cannot be found.');
            return Command::INVALID;
        }

        $outputFolderPath = $this->getOutputPath($outputZipFile);

        $pathToZip = $outputFolderPath . '/' . $outputZipFile;
        file_put_contents($pathToZip, $response->getBody());
        $this->getOutputInterface()->writeln('Downloaded to ' . $pathToZip);

        $zip = new \ZipArchive();

        $zip->open($pathToZip);
        $zip->extractTo($outputFolderPath);
        $zip->close();

        unlink($pathToZip);

        $this->getOutputInterface()->writeln([
            'Unzipped to ' . $outputFolderPath
        ]);

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
        $outputPath = OSHelper::getPathToDeps() . '/php/' . pathinfo($outputFileName, PATHINFO_FILENAME);

        if (!is_dir($outputPath)) {
            mkdir($outputPath, null, true);
        }

        return $outputPath;
    }

    private function createRootInstallationDirectory()
    {
        $path = OSHelper::getPathToDeps() . '/php';

        if (!is_dir($path)) {
            mkdir($path, null, true);
        }
    }
}
