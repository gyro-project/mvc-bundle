<?php

namespace Gyro\Bundle\MVCBundle\Controller\ResultConverter;

use Gyro\MVC\Flash;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class FlashYieldApplier implements ControllerYieldApplier
{
    public function supports($yield) : bool
    {
        return $yield instanceof Flash;
    }

    public function apply($yield, Request $request, Response $response) : void
    {
        if (!$request->hasSession()) {
            return;
        }

        $request->getSession()->getFlashBag()->add($yield->type, $yield->message);
    }
}
