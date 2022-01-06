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
        $releaseUrl = $this->getReleaseUrl();
        $ext = pathinfo($releaseUrl, PATHINFO_EXTENSION);
        $osAsStr = SystemService::toString();
        $outputFileName = $this->getConfig()['version'] . '-' . $osAsStr . '.' . $ext;

        if (!$releaseUrl) {
            $this->getOutput()->writeln('Failed to get release from OS.');
            return Command::FAILURE;
        }

        $this->getOutput()->writeln('Attempting to download from ' . $releaseUrl . ' (' . $osAsStr . ')');

        $response = $this->getGuzzle()->get($releaseUrl);

        if ($response->getStatusCode() < 200 || $response->getStatusCode() > 299) {
            $this->getOutput()->writeln('PHP v' . $this->getConfig()['version'] . ' cannot be found.');

            return Command::INVALID;
        }

        file_put_contents('C:\Users\Script47\Desktop\php\\' . $this->getConfig()['version'] . '.zip', $response->getBody());

        $this->getOutput()->writeln('Downloaded to C:\Users\Script47\Desktop\php\\' . $outputFileName);

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

    public function getReleaseUrl()
    {
        if (in_array(SystemService::getOS(), [SystemService::OS_LINUX, SystemService::OS_OSX])) {
            return $this->getUnixReleaseUrl();
        } else if (SystemService::getOS() === SystemService::OS_WIN) {
            return $this->getWindowsReleaseUrl();
        }

        return null;
    }
}