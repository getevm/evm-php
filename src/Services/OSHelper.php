<?php

namespace Getevm\Evm\Services;

class OSHelper
{
    public static function getPathToDeps()
    {
        $pathToPhpDeps = null;

        switch (SystemService::getOS()) {
            case SystemService::OS_WIN:
                $pathToPhpDeps = $_SERVER['HOMEDRIVE'] . $_SERVER['HOMEPATH'] . '/evm';
                break;

            case SystemService::OS_LINUX:
            case SystemService::OS_OSX:
                $pathToPhpDeps = $_SERVER['HOME'] . '/evm';
                break;
        }

        return $pathToPhpDeps;
    }
}