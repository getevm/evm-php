<?php

namespace Getevm\Evm\Services\Filesystem;

class FileService
{
    /**
     * @param string $path
     * @return mixed
     */
    public function getAsJson(string $path)
    {
        return json_decode(file_get_contents($path), true);
    }

    /**
     * @param string $search
     * @param string $replace
     * @param string $file
     * @return void
     */
    public function replaceInFile(string $search, string $replace, string $file)
    {
        file_put_contents(
            $file,
            str_replace($search, $replace, file_get_contents($file))
        );
    }
}