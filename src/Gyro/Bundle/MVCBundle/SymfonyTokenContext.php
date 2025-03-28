<?php

namespace Gyro\Bundle\MVCBundle;

use Gyro\MVC\TokenContext;
use Gyro\MVC\Exception;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\User\UserInterface;
use Gyro\MVC\Exception\UnauthenticatedUserException;

class SymfonyTokenContext implements TokenContext
{
    public function __construct(private TokenStorageInterface $tokenStorage, private AuthorizationCheckerInterface $authorizationChecker)
    {
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
        return $this->getCurrentUser(UserInterface::class)->getId();
    }

    /**
     * If a security context and token exists, retrieve the username.
     *
     * Throws UnauthenticatedUserException when no valid token exists.
     *
     * @throws UnauthenticatedUserException
     */
    public function getCurrentUsername(): string
    {
        $token = $this->getToken(TokenInterface::class);

        if (Versions::isSecurityVersion6()) {
            /** @psalm-suppress UndefinedInterfaceMethod */
            return $token->getUserIdentifier();
        }

        /** @psalm-suppress UndefinedInterfaceMethod */
        return $token->getUsername();
    }

    /**
     * Get the current User object
     *
     * Throws UnauthenticatedUserException when no valid token exists.
     *
     * @throws UnauthenticatedUserException
     *
     * @template T of UserInterface
     * @psalm-param class-string<T> $expectedClass
     * @psalm-return T
     */
    public function getCurrentUser(string $expectedClass): UserInterface
    {
        $user = $this->getToken(TokenInterface::class)->getUser();

        if (!is_object($user) || !($user instanceof UserInterface) || !($user instanceof $expectedClass)) {
            throw new Exception\UnauthenticatedUserException(sprintf(
                "Expecting user class %s, but got %s",
                $expectedClass,
                get_debug_type($user),
            ));
        }

        return $user;
    }

    public function hasToken(): bool
    {
        return $this->tokenStorage->getToken() !== null;
    }

    public function hasNonAnonymousToken(): bool
    {
        if (Versions::isSecurityVersion6()) {
            return $this->hasToken();
        }

        /** @psalm-suppress UndefinedClass */
        return $this->hasToken() && ! ($this->getToken(TokenInterface::class) instanceof AnonymousToken);
    }

    /**
     * Get the Security Token
     *
     * Throws UnauthenticatedUserException when no valid token exists.
     *
     * @throws UnauthenticatedUserException
     *
     * @template T of TokenInterface
     * @psalm-param class-string<T> $expectedClass
     * @psalm-return T
     */
    public function getToken(string $expectedClass): TokenInterface
    {
        $token = $this->tokenStorage->getToken();

        if ($token === null || !($token instanceof $expectedClass)) {
            throw new Exception\UnauthenticatedUserException(sprintf(
                "Expecting token class %s, but got %s",
                $expectedClass,
                is_object($token) ? get_class($token) : gettype($token)
            ));
        }

        return $token;
    }

    /**
     * @param mixed $attributes
     */
    public function isGranted($attributes, ?object $object = null): bool
    {
        return $this->authorizationChecker->isGranted($attributes, $object);
    }

    /**
     * @param mixed $attributes
     */
    public function assertIsGranted($attributes, ?object $object = null): void
    {
        if (!$this->isGranted($attributes, $object)) {
            throw new AccessDeniedHttpException();
        }
    }
}
