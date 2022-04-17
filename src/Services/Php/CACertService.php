<?php

namespace Getevm\Evm\Services\Php;

use Getevm\Evm\Services\Http\CurlDownloaderService;

class CACertService
{
    const CA_CERT_URL = 'https://curl.haxx.se/ca/cacert.pem';

    /**
     * @var CurlDownloaderService
     */
    private $curlDownloaderService;

    /**
     * @param CurlDownloaderService $curlDownloaderService
     */
    public function __construct(CurlDownloaderService $curlDownloaderService)
    {
        $this->curlDownloaderService = $curlDownloaderService;
    }

    /**
     * @return null|string
     */
    public function download(): ?string
    {
        $response = $this->curlDownloaderService->download(self::CA_CERT_URL);

        if (!$response) {
            return null;
        }

        return $response;
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
