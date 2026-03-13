<?php

namespace Gyro\MVC\EventDispatcher;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EventDispatcher
{
    public function __construct(private EventDispatcherInterface $eventDispatcher)
    {
    }

    public function dispatch(object $event, ?string $eventName = null): void
    {
        /** @psalm-suppress RiskyTruthyFalsyComparison */
        $eventName = $eventName ?: $event::class;

        /** @psalm-suppress TooManyArguments, InvalidArgument */
        $this->eventDispatcher->dispatch($event, $eventName);
    }
}
