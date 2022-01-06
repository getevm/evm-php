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
        $this->getOutput()->writeln(PHP_OS);
        $this->getOutput()->writeln('Attempting to download from ' . $this->getWindowsRelease());

        $response = $this->getGuzzle()
            ->get($this->getWindowsRelease());

        if ($response->getStatusCode() === 400) {
            $this->getOutput()->writeln('PHP v' . $this->getConfig()['version'] . ' cannot be found.');

            return Command::INVALID;
        }

        file_put_contents('C:\Users\Script47\Desktop\php\\' . $this->getConfig()['version'] . '.zip', $response->getBody());

        $this->getOutput()->writeln('Downloaded to C:\Users\Script47\Desktop\php\\' . $this->getConfig()['version'] . '.tar.gz');

        return Command::SUCCESS;
    }

    public function getUnixRelease()
    {
//        return 'https://www.php.net/distributions/php-' . $this->getConfig()['version'] . '.tar.gz';
        return 'https://museum.php.net/php8/php-' . $this->getConfig()['version'] . '.tar.gz';
    }

    public function getWindowsRelease()
    {
        $url = 'https://windows.php.net/downloads/releases/archives/php-';
        $url .= $this->getConfig()['version'];
        $url .= $this->getConfig()['nts'] ? '-nts' : '';
        $url .= '-Win32-vs16-';
        $url .= $this->getConfig()['archType'];
        $url .= '.zip';

        return $url;
    }
}