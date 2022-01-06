<?php

namespace Getevm\Evm\Services;

class SystemService
{

    const OS_UNKNOWN = 1;
    const OS_WIN = 2;
    const OS_LINUX = 3;
    const OS_OSX = 4;

    /**
     * @return int
     */
    public static function getOS()
    {
        switch (true) {
            case stristr(PHP_OS, 'DAR'):
                return self::OS_OSX;
            case stristr(PHP_OS, 'WIN'):
                return self::OS_WIN;
            case stristr(PHP_OS, 'LINUX'):
                return self::OS_LINUX;
            default :
                return self::OS_UNKNOWN;
        }
    }

    public static function toString()
    {
        return [
            self::OS_UNKNOWN => 'Unknown',
            self::OS_WIN => 'Windows',
            self::OS_LINUX => 'Linux',
            self::OS_OSX => 'OSX'
        ][self::getOS()];
    }
}