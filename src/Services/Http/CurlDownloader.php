<?php

namespace Getevm\Evm\Services\Http;

class CurlDownloader
{
    public function download(string $url, callable $progressFunction = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);

        if (is_callable($progressFunction)) {
            function progress($resource, $downloadTotal, $downloaded, $uploadTotal, $uploaded) {
                global $progressFunction;
                $progressFunction($resource, $downloadTotal, $downloaded, $uploadTotal, $uploaded);
            }

            curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, 'progress');
            curl_setopt($ch, CURLOPT_NOPROGRESS, false);
        }

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}
