<?php

namespace Gyro\Bundle\MVCBundle\ParamConverter;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SymfonyServiceProvider implements ServiceProviderInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFormFactory() : FormFactoryInterface
    {
        return $this->container->get('form.factory');
    }

    public function getTokenStorage() : TokenStorageInterface
    {
        return $this->container->get('security.token_storage');
    }

    public function getAuthorizationChecker() : AuthorizationCheckerInterface
    {
        return $this->container->get('security.authorization_checker');
    }
}
