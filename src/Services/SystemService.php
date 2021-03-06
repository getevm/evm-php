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
    public static function getOS(): int
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

    /**
     * @return string
     */
    public static function getOSAsString(): string
    {
        return [
            self::OS_UNKNOWN => 'Unknown',
            self::OS_WIN => 'Windows',
            self::OS_LINUX => 'Linux',
            self::OS_OSX => 'OSX'
        ][self::getOS()];
    }

    /**
     * @return string
     */
    public static function getArchType(): string
    {
        return PHP_INT_SIZE === 8 ? 'x64' : 'x86';
    }

    /**
     * @return string|null
     */
    public static function getOSType(): ?string
    {
        switch (SystemService::getOS()) {
            case SystemService::OS_WIN:
                return 'nt';

            case SystemService::OS_LINUX:
            case SystemService::OS_OSX:
                return 'nix';

            default:
                return null;
        }
    }
}