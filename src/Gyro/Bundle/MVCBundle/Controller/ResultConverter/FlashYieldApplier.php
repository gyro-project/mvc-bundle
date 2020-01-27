<?php

namespace Gyro\Bundle\MVCBundle\Controller\ResultConverter;

use Gyro\MVC\Flash;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class FlashYieldApplier implements ControllerYieldApplier
{
    /**
     * @param mixed $yield
     */
    public function supports($yield) : bool
    {
        return $yield instanceof Flash;
    }

    /**
     * @param mixed $yield
     */
    public function apply($yield, Request $request, Response $response) : void
    {
        assert($yield instanceof Flash);

        if (!$request->hasSession()) {
            return;
        }

        $session = $request->getSession();

        assert($session instanceof Session);

        $session->getFlashBag()->add($yield->type, $yield->message);
    }
}
