<?php

namespace Gyro\MVC\EventDispatcher;

use Gyro\MVC\SymfonyVersion;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\Event;

class EventDispatcherTest extends TestCase
{
    public function testDispatchDelegatesVersion4Dot4AndAbove()
    {
        if (! SymfonyVersion::isVersion4Dot4AndAbove()) {
            $this->markTestSkipped('Only for Symfony v4.4+');
        }

        $dispatcher = new EventDispatcher(
            $mock = \Phake::mock(EventDispatcherInterface::class)
        );

        $event = new \stdclass;

        \Phake::when($mock)->dispatch(\Phake::anyParameters())->thenReturn($event);

        $dispatcher->dispatch($event);

        \Phake::verify($mock)->dispatch($event, 'stdClass');
    }

    public function testDispatchDelegatesVersion4Dot4Below()
    {
        if (SymfonyVersion::isVersion4Dot4AndAbove()) {
            $this->markTestSkipped('Only for Symfony v4.4+');
        }

        $dispatcher = new EventDispatcher(
            $mock = \Phake::mock(EventDispatcherInterface::class)
        );

        $event = new Event();

        $dispatcher->dispatch($event);

        \Phake::verify($mock)->dispatch(get_class($event), $event);
    }
}
