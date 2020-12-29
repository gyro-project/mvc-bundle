<?php

namespace Gyro\MVC;

use PackageVersions\Versions;

class SymfonyVersion
{
    public static function isVersion4Dot4AndAbove(): bool
    {
        $packages = Versions::VERSIONS;
        $package = isset($packages['symfony/framework-bundle'])
            ? 'symfony/framework-bundle'
            : 'symfony/symfony';

        $version = str_replace('v', '', Versions::getVersion($package));
        $pos = strpos($version, '@');

        if ($pos !== false) {
            $version = substr($version, 0, $pos);
        }

        return version_compare('4.4.0', $version, '<=');
    }
}
