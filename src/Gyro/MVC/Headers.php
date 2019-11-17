<?php

namespace Gyro\MVC;

class Headers
{
    public $values = [];

    public function __construct(array $values)
    {
        $this->values = $values;
    }
}
