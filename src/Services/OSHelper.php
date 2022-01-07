<?php

namespace Getevm\Evm\Services;

class OSHelper
{
    public static function getPathToDeps()
    {
        switch (SystemService::getOS()) {
            case SystemService::OS_WIN:
                return $_SERVER['HOMEDRIVE'] . $_SERVER['HOMEPATH'] . '/evm';

            case SystemService::OS_LINUX:
            case SystemService::OS_OSX:
                return $_SERVER['HOME'] . '/evm';

            default:
                return null;
        }
    }
}