<?php

namespace Gyro\MVC\EventDispatcher;

use Gyro\MVC\SymfonyVersion;
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

        if (SymfonyVersion::isVersion4Dot4AndAbove()) {
            /** @psalm-suppress TooManyArguments, InvalidArgument */
            $this->eventDispatcher->dispatch($event, $eventName);
        } else {
            /** @psalm-suppress TooManyArguments, InvalidArgument, ArgumentTypeCoercion */
            $this->eventDispatcher->dispatch($eventName, $event);
        }
    }
}
