<?php

namespace Gyro\Bundle\MVCBundle\Controller\ResultConverter;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

interface ControllerResultConverter
{
    /**
     * @param mixed $result
     */
    public function supports($result) : bool;

    /**
     * @param mixed $result
     */
    public function convert($result, Request $request) : Response;
}
