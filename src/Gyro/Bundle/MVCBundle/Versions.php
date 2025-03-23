<?php

namespace Gyro\Bundle\MVCBundle;

use Composer\InstalledVersions;

class Versions
{
    public static function isSecurityVersion6(): bool
    {
        $version = null;
        if (InstalledVersions::isInstalled('symfony/symfony')) {
            $version = InstalledVersions::getVersion('symfony/symfony');
        } elseif (InstalledVersions::isInstalled('symfony/security')) {
            $version = InstalledVersions::getVersion('symfony/security');
        }

        if ($version !== null && version_compare($version, '6.0.0') >= 0) {
            return true;
        }

        return false;
    }

    public static function hasMainRequestConstant(): bool
    {
        $version = null;
        if (InstalledVersions::isInstalled('symfony/symfony')) {
            $version = InstalledVersions::getVersion('symfony/symfony');
        } elseif (InstalledVersions::isInstalled('symfony/http-kernel')) {
            $version = InstalledVersions::getVersion('symfony/http-kernel');
        }

        if ($version !== null && version_compare($version, '7.0.0') >= 0) {
            return true;
        }

        return false;
    }
}
