<?php

namespace Gyro\MVC;

interface TokenContext
{
    /**
     * If a security context and token exists, retrieve the user id.
     *
     * Throws UnauthenticatedUserException when no valid token exists.
     *
     * @return string|integer
     */
    public function getCurrentUserId();

    /**
     * If a security context and token exists, retrieve the username.
     *
     * Throws UnauthenticatedUserException when no valid token exists.
     *
     * @throws \Gyro\MVC\Exception\UnauthenticatedUserException
     * @return string
     */
    public function getCurrentUsername();

    /**
     * Get the current User object
     *
     * Throws UnauthenticatedUserException when no valid token exists.
     *
     * @throws \Gyro\MVC\Exception\UnauthenticatedUserException
     * @return \Symfony\Component\Security\Core\User\UserInterface
     */
    public function getCurrentUser();

    /**
     * @return bool
     */
    public function hasToken();

    /**
     * @return bool
     */
    public function hasNonAnonymousToken();

    /**
     * Get the Security Token
     *
     * Throws UnauthenticatedUserException when no valid token exists.
     *
     * @throws \Gyro\MVC\Exception\UnauthenticatedUserException
     * @return \Symfony\Component\Securite\Core\Authentication\Token\TokenInterface
     */
    public function getToken();

    /**
     * @param mixed $attributes
     * @param mixed $object
     * @return bool
     */
    public function isGranted($attributes, $object = null);

    /**
     * @param mixed $attributes
     * @param mixed $object
     * @throws AccessDeniedHttpException
     */
    public function assertIsGranted($attributes, $object = null);
}
