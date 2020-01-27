<?php

namespace Gyro\Bundle\MVCBundle\View;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\Bundle;

class BundleLocation
{
    private $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    public function locationFor($className) : ?string
    {
        $bundle = $this->getBundleForClass($className);

        if (!$bundle) {
            return null;
        }

        return $bundle->getName();
    }

    protected function getBundleForClass(string $class) : ?Bundle
    {
        $reflectionClass = new \ReflectionClass($class);
        $bundles = $this->kernel->getBundles();

        do {
            $namespace = $reflectionClass->getNamespaceName();
            foreach ($bundles as $bundle) {
                if (strpos($namespace, $bundle->getNamespace()) === 0) {
                    return $bundle;
                }
            }

            $reflectionClass = $reflectionClass->getParentClass();
        } while ($reflectionClass);

        return null;
    }
}
