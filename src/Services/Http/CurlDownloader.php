<?php

namespace Getevm\Evm\Services\Http;

class CurlDownloader
{
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

    public function progressFunction($resource, $downloadTotal, $downloaded, $uploadTotal, $uploaded)
    {
        if ($downloadTotal > 0) {
            echo $downloaded / $downloadTotal * 100 . '%' . PHP_EOL;
        } else {
            echo json_encode([
                $resource,
                $downloadTotal,
                $downloaded,
                $uploadTotal,
                $uploaded
            ]);
        }
    }
}
