<?php

namespace Getevm\Evm\Services;

class OSHelper
{
    public static function getPathToDeps()
    {
        switch (SystemService::getOS()) {
            case SystemService::OS_WIN:
                return $_SERVER['HOMEDRIVE'] . $_SERVER['HOMEPATH'] . DIRECTORY_SEPARATOR . 'evm';

            case SystemService::OS_LINUX:
            case SystemService::OS_OSX:
                return $_SERVER['HOME'] . DIRECTORY_SEPARATOR . 'evm';

            default:
                return null;
        }
    }
}