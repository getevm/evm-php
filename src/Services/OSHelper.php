<?php

namespace Getevm\Evm\Services;

class OSHelper
{
    public static function getPathToDeps()
    {
        switch (SystemService::getOS()) {
            case SystemService::OS_WIN:
                $path = $_SERVER['HOMEDRIVE'] . DIRECTORY_SEPARATOR . 'evm';
                break;
            case SystemService::OS_LINUX:
            case SystemService::OS_OSX:
                $path = $_SERVER['HOME'] . DIRECTORY_SEPARATOR . 'evm';
                break;
        }

        if (!isset($path)) {
            return null;
        }

        if (!is_dir($path)) {
            mkdir($path, null, true);

            return $path;
        }

        return $path;
    }
}