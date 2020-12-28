<?php

namespace Gyro\MVC;

use PackageVersions\Versions;

class SymfonyVersion
{
    public static function isVersion4Dot4AndAbove(): bool
    {
        $version = str_replace('v', '', Versions::getVersion('symfony/symfony'));
        $pos = strpos($version, '@');

        if ($pos !== false) {
            $version = substr($version, 0, $pos);
        }

        return version_compare('4.4.0', $version, '<=');
    }
}
