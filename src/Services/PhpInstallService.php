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
        $this->getOutput()->writeln('Attempting to download from ' . $this->getUnixRelease());

        $response = $this->getGuzzle()
            ->get($this->getUnixRelease());

        if ($response->getStatusCode() === 400) {
            $this->getOutput()->writeln('PHP v' . $this->getConfig()['version'] . ' cannot be found.');

            return Command::INVALID;
        }

        return Command::SUCCESS;
    }

    public function getUnixRelease()
    {
//        return 'https://www.php.net/distributions/php-' . $this->getConfig()['version'] . '.tar.gz';
        return 'https://museum.php.net/php8/php-' . $this->getConfig()['version'] . '.tar.gz';
    }

    public function getWindows()
    {

    }
}