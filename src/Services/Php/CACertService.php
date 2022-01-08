<?php

namespace Getevm\Evm\Services\Php;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class CACertService
{
    const CA_CERT_URL = 'https://curl.haxx.se/ca/cacert.pem';

    /**
     * @return null|string
     * @throws GuzzleException
     */
    public function download(): ?string
    {
        $client = new Client();
        $response = $client->get(self::CA_CERT_URL);

        if ($response->getStatusCode() !== 200) {
            return null;
        }

        return $response->getBody()->getContents();
    }

    /**
     * @param string $path
     * @param string $cert
     * @return bool
     */
    public function store(string $path, string $cert): bool
    {
        if (!is_dir($path)) {
            mkdir($path, null, true);
        }

        return file_put_contents($path . DIRECTORY_SEPARATOR . 'cacert.pem', $cert) !== false;
    }
}