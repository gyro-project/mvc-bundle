<?php

namespace Gyro\Bundle\MVCBundle\ParamConverter;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

interface ServiceProvider
{
    public function getFormFactory() : FormFactoryInterface;

    public function getTokenStorage() : TokenStorageInterface;

    public function getAuthorizationChecker() : AuthorizationCheckerInterface;
}
