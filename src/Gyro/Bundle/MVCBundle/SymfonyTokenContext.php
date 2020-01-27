<?php

namespace Gyro\Bundle\MVCBundle;

use Gyro\MVC\TokenContext;
use Gyro\MVC\Exception;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class SymfonyTokenContext implements TokenContext
{
    private $tokenStorage;
    private $authorizationChecker;

    public function __construct(TokenStorageInterface $tokenStorage, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * If a security context and token exists, retrieve the user id.
     *
     * Throws UnauthenticatedUserException when no valid token exists.
     *
     * @return string|int
     *
     * @psalm-suppress UndefinedInterfaceMethod
     */
    public function getCurrentUserId()
    {
        return $this->getCurrentUser()->getId();
    }

    /**
     * If a security context and token exists, retrieve the username.
     *
     * Throws UnauthenticatedUserException when no valid token exists.
     *
     * @throws \Gyro\MVC\Exception\UnauthenticatedUserException
     */
    public function getCurrentUsername() : string
    {
        return $this->getToken()->getUsername();
    }

    /**
     * Get the current User object
     *
     * Throws UnauthenticatedUserException when no valid token exists.
     *
     * @throws \Gyro\MVC\Exception\UnauthenticatedUserException
     */
    public function getCurrentUser() : \Symfony\Component\Security\Core\User\UserInterface
    {
        $user = $this->getToken()->getUser();

        if (!is_object($user)) {
            throw new Exception\UnauthenticatedUserException();
        }

        return $user;
    }

    public function hasToken() : bool
    {
        return $this->tokenStorage->getToken() !== null;
    }

    public function hasNonAnonymousToken() : bool
    {
        return $this->hasToken() && ! ($this->getToken() instanceof AnonymousToken);
    }

    /**
     * Get the Security Token
     *
     * Throws UnauthenticatedUserException when no valid token exists.
     *
     * @throws \Gyro\MVC\Exception\UnauthenticatedUserException
     */
    public function getToken() : \Symfony\Component\Security\Core\Authentication\Token\TokenInterface
    {
        $token = $this->tokenStorage->getToken();

        if ($token === null) {
            throw new Exception\UnauthenticatedUserException();
        }

        return $token;
    }

    /**
     * @param mixed $attributes
     */
    public function isGranted($attributes, ?object $object = null) : bool
    {
        return $this->authorizationChecker->isGranted($attributes, $object);
    }

    /**
     * @param mixed $attributes
     */
    public function assertIsGranted($attributes, ?object $object = null) : void
    {
        if (!$this->isGranted($attributes, $object)) {
            throw new AccessDeniedHttpException();
        }
    }
}
