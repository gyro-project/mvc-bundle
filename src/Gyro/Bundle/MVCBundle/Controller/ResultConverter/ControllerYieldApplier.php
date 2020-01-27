<?php

namespace Gyro\Bundle\MVCBundle\Controller\ResultConverter;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

interface ControllerYieldApplier
{
    /**
     * @param mixed $yield
     */
    public function supports($yield) : bool;

    /**
     * @param mixed $yield
     */
    public function apply($yield, Request $request, Response $response) : void;
}
