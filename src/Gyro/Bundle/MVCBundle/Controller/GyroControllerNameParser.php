<?php

namespace Gyro\Bundle\MVCBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GyroControllerNameParser
{
    private $symfonyParser;
    private $container;

    public function __construct(ControllerNameParser $parser, ContainerInterface $container)
    {
        $this->symfonyParser = $parser;
        $this->container = $container;
    }

    public function parse(string $controller) : string
    {
        $parts = explode(":", $controller);

        if (count($parts) === 3) {
            return $this->symfonyParser->parse($controller);
        }

        if (count($parts) !== 2) {
            throw new \RuntimeException("Cannot parse controller name");
        }

        return $this->parseServiceController($parts[0], $parts[1]);
    }

    private function parseServiceController(string $serviceId, string $method) : string
    {
        $service = $this->container->get($serviceId);

        return \Doctrine\Common\Util\ClassUtils::getClass($service) . '::' . $method;
    }
}
