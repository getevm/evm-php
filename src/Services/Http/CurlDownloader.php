<?php

namespace Getevm\Evm\Services\Http;

class CurlDownloader
{
    public function download(string $url, callable $progressFunction, callable $errorFunction)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, $progressFunction);
        curl_setopt($ch, CURLOPT_NOPROGRESS, false);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}
