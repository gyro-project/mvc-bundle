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

    public function dispatch(object $event, ?string $eventName = null): void
    {
        $eventName = $eventName ?: get_class($event);

        /** @psalm-suppress TooManyArguments, InvalidArgument */
        $this->eventDispatcher->dispatch($event, $eventName);
    }
}
