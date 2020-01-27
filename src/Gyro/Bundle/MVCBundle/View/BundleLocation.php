<?php

namespace Gyro\Bundle\MVCBundle\View;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class BundleLocation
{
    private $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @param class-string $className
     */
    public function locationFor(string $className) : ?string
    {
        $bundle = $this->getBundleForClass($className);

        if (!$bundle) {
            return null;
        }

        return $bundle->getName();
    }

    /**
     * @param class-string $className
     */
    protected function getBundleForClass(string $className) : ?BundleInterface
    {
        $reflectionClass = new \ReflectionClass($className);
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
