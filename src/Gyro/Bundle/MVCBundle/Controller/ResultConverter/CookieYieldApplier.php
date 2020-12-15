<?php

namespace Gyro\Bundle\MVCBundle\Controller\ResultConverter;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class CookieYieldApplier implements ControllerYieldApplier
{
    /**
     * @param mixed $yield
     */
    public function supports($yield): bool
    {
        return $yield instanceof Cookie;
    }

    /**
     * @param mixed $yield
     */
    public function apply($yield, Request $request, Response $response): void
    {
        assert($yield instanceof Cookie);

        $response->headers->setCookie($yield);
    }
}
