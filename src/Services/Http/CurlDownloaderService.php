<?php

namespace Getevm\Evm\Services\Http;

use Getevm\Evm\Services\Console\ConsoleOutputService;

class CurlDownloaderService
{
    /**
     * @var ConsoleOutputService
     */
    private $consoleOutputService;

    /**
     * @param ConsoleOutputService $consoleOutputService
     */
    public function __construct(ConsoleOutputService $consoleOutputService)
    {
        $this->consoleOutputService = $consoleOutputService;
    }

    public function download(string $url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_NOPROGRESS, false);
        curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, [$this, 'progressFunction']);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $response = curl_exec($ch);

        if ($response === false) {
            $response = null;
        }

        curl_close($ch);

        return $response;
    }
}
