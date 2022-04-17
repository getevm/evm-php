<?php

namespace Getevm\Evm\Services\Http;

class CurlDownloader
{
    private $progressFunction = null;

    public function download(string $url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, [$this, 'progressFunction']);
        curl_setopt($ch, CURLOPT_NOPROGRESS, false);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public function setProgressFunction(callable $func)
    {
        $this->progressFunction = $func;
    }
}
