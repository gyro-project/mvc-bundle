<?php

namespace Gyro\Bundle\MVCBundle;

use Gyro\MVC\TokenContext;
use Gyro\MVC\Exception\UnauthenticatedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class MockTokenContext implements TokenContext
{
    private $user;

    public function __construct(UserInterface $user = null)
    {
        $this->user = $user;
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
        if ($this->user === null) {
            throw new UnauthenticatedUserException();
        }

        return $this->user->getId();
    }

    /**
     * If a security context and token exists, retrieve the username.
     *
     * Throws UnauthenticatedUserException when no valid token exists.
     *
     * @throws \Gyro\MVC\Exception\UnauthenticatedUserException
     */
    public function getCurrentUsername(): string
    {
        if ($this->user === null) {
            throw new UnauthenticatedUserException();
        }

        if (Versions::isSecurityVersion6()) {
            /** @psalm-suppress UndefinedInterfaceMethod */
            return $this->user->getUserIdentifier();
        }

        /** @psalm-suppress UndefinedInterfaceMethod */
        return $this->user->getUsername();
    }

    /**
     * Get the current User object
     *
     * Throws UnauthenticatedUserException when no valid token exists.
     *
     * @throws \Gyro\MVC\Exception\UnauthenticatedUserException
     *
     * @template T of UserInterface
     * @psalm-param class-string<T> $expectedClass
     * @psalm-return T
     */
    public function getCurrentUser(string $expectedClass): \Symfony\Component\Security\Core\User\UserInterface
    {
        if (!is_object($this->user) || !($this->user instanceof  $expectedClass)) {
            throw new UnauthenticatedUserException();
        }

        return $this->user;
    }

    public function hasToken(): bool
    {
        return true;
    }

    public function hasNonAnonymousToken(): bool
    {
        return false;
    }

    public function getToken(string $expectedClass): \Symfony\Component\Security\Core\Authentication\Token\TokenInterface
    {
        throw new \BadMethodCallException("getToken() not supported in MockTokenContext");
    }

    /**
     * @param mixed $attributes
     *
     * @psalm-suppress DeprecatedClass
     */
    public function isGranted($attributes, ?object $object = null): bool
    {
        if (!is_string($attributes) && strpos($attributes, 'ROLE_') === false) {
            throw new \BadMethodCallException("Only ROLE_* checks are possible with mock interface.");
        }

        if ($this->user === null) {
            throw new UnauthenticatedUserException();
        }

        $roles = $this->user->getRoles();

        foreach ($roles as $role) {
            if ($role === $attributes) {
                return true;
            }
        }

        return false;
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
