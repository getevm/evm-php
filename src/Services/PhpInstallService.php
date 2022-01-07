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

        $log = [];

        $paths = array_map(function ($path) use ($outputFolderPath) {
            $phpBinaryWithoutExt = pathinfo(PHP_BINARY, PATHINFO_BASENAME);

            if ($path !== $phpBinaryWithoutExt) {
                return $path;
            }

            return $outputFolderPath;
        }, $this->getPathVariable());

        $log['old_paths'] = $this->getPathVariable();
        $log['new_paths'] = $paths;

        file_put_contents($this->getPathToDeps() . '/' . time() . '.json', json_encode($log));

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
