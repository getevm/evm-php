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
        $this->getOutputInterface()->writeln([
            'OS: ' . ($this->getConfig()['os'] . ' (' . $this->getConfig()['osType'] . ')'),
            'Architecture: ' . $this->getConfig()['archType'],
            'Thread Safety: ' . ($this->getConfig()['ts'] ? 'Yes' : 'No')
        ]);

        $this->createRootInstallationDirectory();

        $this->getOutputInterface()->writeln([
            'Finding appropriate release...'
        ]);

        $releaseUrl = $this->getReleaseUrl();

        if (!$releaseUrl) {
            $this->getOutputInterface()->writeln('Failed to find release.');
            return Command::FAILURE;
        }

        $ext = pathinfo($releaseUrl, PATHINFO_EXTENSION);
        $outputZipFile = $this->buildOutputFileName($ext);

        $this->getOutputInterface()->writeln([
            'Release found, attempting to download from ' . $releaseUrl
        ]);

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

        $this->getOutputInterface()->writeln([
            'Unzipped to ' . $outputFolderPath
        ]);

        unlink($pathToZip);

        return Command::SUCCESS;
    }

    private function getUnixReleaseUrl()
    {
        $majorVersion = explode('.', $this->getConfig()['version'])[0];
        return 'https://museum.php.net/php' . $majorVersion . '/php-' . $this->getConfig()['version'] . '.tar.gz';
    }

    private function getWindowsReleaseUrl()
    {
        $url = 'https://windows.php.net/downloads/releases/archives/php-';
        $url .= $this->getConfig()['version'];
        $url .= !$this->getConfig()['ts'] ? '-nts' : '';
        $url .= '-Win32-vs16-';
        $url .= $this->getConfig()['archType'];
        $url .= '.zip';

        return $url;
    }

    private function buildOutputFileName($ext)
    {
        $fileName = $this->getConfig()['version'];

        if (!$this->getConfig()['ts']) {
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
        return $this->findRelease();
    }

    private function findRelease()
    {
        $config = $this->getConfig();
        $releasesByOSType = json_decode(file_get_contents(__DIR__ . '/../../data/php.json'), true)[$config['osType']];

        switch ($config['osType']) {
            case 'nt':
                $release = array_values(array_filter($releasesByOSType, function ($release) use ($config) {
                    $fileName = pathinfo($release, PATHINFO_FILENAME);

                    if (strpos($fileName, '-nts-') === false) {
                        list($php, $version, $win, $vcvs, $archType) = explode('-', $fileName);

                    } else {
                        list($php, $version, $nts, $win, $vcvs, $archType) = explode('-', $fileName);

                    }

                    $versionCheck = $version === $config['version'];
                    $archTypeCheck = $archType === $config['archType'];
                    $ntsCheck = !$config['ts'] && isset($nts) && $nts === 'nts';

                    return $versionCheck && $archTypeCheck && $ntsCheck;
                }));

                if (empty($release)) {
                    return null;
                }

                return 'https://windows.php.net/downloads/releases/archives/' . $release[0];

            case 'nix':
                return ' ';

            default:
                return null;
        }
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
