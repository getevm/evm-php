<?php

namespace Getevm\Evm\Services\Filesystem;

class FileService
{
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