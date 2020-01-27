<?php

namespace Gyro\MVC;

class Headers
{
    /** @var array<string,string> */
    public $values = [];

    /**
     * @param array<string,string> $values
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }
}
