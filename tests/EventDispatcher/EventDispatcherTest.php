<?php

namespace Gyro\MVC\EventDispatcher;

use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EventDispatcherTest extends TestCase
{
    public function testDispatchDelegates()
    {
        $dispatcher = new EventDispatcher(
            $mock = \Phake::mock(EventDispatcherInterface::class)
        );

        $event = \Phake::mock(Event::class);

        \Phake::when($event)->getEventName()->thenReturn('foo');

        $dispatcher->dispatch($event);

        \Phake::verify($mock)->dispatch($event, 'foo');
    }
}
