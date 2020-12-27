<?php

namespace Gyro\MVC\EventDispatcher;

abstract class Event
{
    public function getEventName(): string
    {
        return static::class;
    }
}
