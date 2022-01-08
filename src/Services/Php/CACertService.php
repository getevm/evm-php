<?php

namespace Getevm\Evm\Services\Php;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class CACertService
{
    const CA_CERT_URL = 'https://curl.haxx.se/ca/cacert.pem';

    /**
     * @throws GuzzleException
     */
    public function download()
    {
        $client = new Client();
        $response = $client->get(self::CA_CERT_URL);

        if ($response->getStatusCode() !== 200) {
            return;
        }

        return $response->getBody();
    }

    public function store()
    {

    }
}