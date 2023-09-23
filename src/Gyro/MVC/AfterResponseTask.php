<?php

namespace Gyro\MVC;

class AfterResponseTask
{
    private $callable;

    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    public function __invoke()
    {
        $callable = $this->callable;
        $callable();
    }
}
