<?php

namespace Gyro\MVC;

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
     */
    public function getCurrentUser() : \Symfony\Component\Security\Core\User\UserInterface;

    public function hasToken() : bool;

    public function hasNonAnonymousToken() : bool;

    /**
     * Get the Security Token
     *
     * Throws UnauthenticatedUserException when no valid token exists.
     *
     * @throws \Gyro\MVC\Exception\UnauthenticatedUserException
     */
    public function getToken() : \Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

    /**
     * @param mixed $attributes
     */
    public function isGranted($attributes, ?object $object = null) : bool;

    /**
     * @param mixed $attributes
     * @param mixed $object
     *
     * @throws AccessDeniedHttpException
     */
    public function assertIsGranted($attributes, ?object $object = null) : void;
}
