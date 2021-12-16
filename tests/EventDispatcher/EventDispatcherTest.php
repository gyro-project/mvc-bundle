<?php

namespace Gyro\MVC\EventDispatcher;

use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\Event;

class EventDispatcherTest extends TestCase
{
    public function testDispatchDelegatesVersion4Dot4AndAbove()
    {
        $dispatcher = new EventDispatcher(
            $mock = \Phake::mock(EventDispatcherInterface::class)
        );

        $event = new \stdclass;

        \Phake::when($mock)->dispatch(\Phake::anyParameters())->thenReturn($event);

        $dispatcher->dispatch($event);

        \Phake::verify($mock)->dispatch($event, 'stdClass');
    }
}
