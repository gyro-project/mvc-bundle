<?php

namespace Gyro\MVC\EventDispatcher;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EventDispatcher
{
    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function dispatch(Event $event): void
    {
        /** @psalm-suppress TooManyArguments */
        $this->eventDispatcher->dispatch($event, $event->getEventName());
    }
}
