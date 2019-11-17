<?php

namespace Gyro\Bundle\MVCBundle\Controller\ResultConverter;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

interface ControllerResultConverter
{
    public function supports($result) : bool;
    public function convert($result, Request $request) : Response;
}
