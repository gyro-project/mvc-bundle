<?php

namespace Gyro\MVC;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

interface TokenContext
{
    /**
     * If a security context and token exists, retrieve the user id.
     *
     * Throws UnauthenticatedUserException when no valid token exists.
     *
     * @return string|int
     */
    public function getCurrentUserId();

    /**
     * If a security context and token exists, retrieve the username.
     *
     * Throws UnauthenticatedUserException when no valid token exists.
     *
     * @throws \Gyro\MVC\Exception\UnauthenticatedUserException
     */
    public function getCurrentUsername() : string;

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
    public function getCurrentUser(string $expectedClass) : UserInterface;

    public function hasToken() : bool;

    public function hasNonAnonymousToken() : bool;

    /**
     * Get the Security Token
     *
     * Throws UnauthenticatedUserException when no valid token exists.
     *
     * @throws \Gyro\MVC\Exception\UnauthenticatedUserException
     *
     * @template T of TokenInterface
     * @psalm-param class-string<T> $expectedClass
     * @psalm-return T
     */
    public function getToken(string $expectedClass) : TokenInterface;

    /**
     * @param mixed $attributes
     */
    public function isGranted($attributes, ?object $object = null) : bool;

    /**
     * @param mixed $attributes
     *
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    public function assertIsGranted($attributes, ?object $object = null) : void;
}
