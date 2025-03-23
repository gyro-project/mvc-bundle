<?php

namespace Gyro\Bundle\MVCBundle\ParamConverter;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use RuntimeException;

class SymfonyServiceProvider implements ServiceProvider
{
    public function __construct(private ?FormFactoryInterface $formFactory, private ?TokenStorageInterface $tokenStorage, private ?AuthorizationCheckerInterface $authorizationChecker)
    {
    }

    public function getFormFactory(): FormFactoryInterface
    {
        return $this->throwOnNull($this->formFactory);
    }

    public function getTokenStorage(): TokenStorageInterface
    {
        return $this->throwOnNull($this->tokenStorage);
    }

    public function getAuthorizationChecker(): AuthorizationCheckerInterface
    {
        return $this->throwOnNull($this->authorizationChecker);
    }

    private function throwOnNull(?object $service): object
    {
        if ($service === null) {
            throw new RuntimeException("Non-existant service");
        }

        return $service;
    }
}
