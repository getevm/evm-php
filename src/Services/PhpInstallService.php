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
        $this->getOutput()->writeln('Requesting PHP v' . $this->getConfig()['version']);

        $response = $this->getGuzzle()->get($this->getUnixRelease(), [
            'headers' => [
                'Content-Type' => 'application/octet-stream'
            ]
        ]);

        if ($response->getStatusCode() === 400) {
            $this->getOutput()->writeln('PHP v' . $this->getConfig()['version'] . ' cannot be found.');

            return Command::INVALID;
        }

        return Command::SUCCESS;
    }

    public function getUnixRelease()
    {
        return 'https://www.php.net/distributions/php-' . $this->getConfig()['version'] . '.tar.gz';
    }

    public function getWindows()
    {

    }
}