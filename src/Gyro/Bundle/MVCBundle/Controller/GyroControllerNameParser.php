<?php

namespace Gyro\Bundle\MVCBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;

class GyroControllerNameParser
{
    private \Symfony\Component\DependencyInjection\ContainerInterface $container;

    /**
     * @psalm-suppress DeprecatedClass
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function parse(string $controller): string
    {
        $parts = explode(":", $controller);

        if (count($parts) !== 2) {
            throw new \RuntimeException("Cannot parse controller name");
        }

        return $this->parseServiceController($parts[0], $parts[1]);
    }

    private function parseServiceController(string $serviceId, string $method): string
    {
        $service = $this->container->get($serviceId, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE);
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        \assert(\is_object($service));

        return get_class($service) . '::' . $method;
    }
}
