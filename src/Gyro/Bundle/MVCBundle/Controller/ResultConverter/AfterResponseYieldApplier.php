<?php

namespace Gyro\Bundle\MVCBundle\Controller\ResultConverter;

use Gyro\MVC\AfterResponseTask;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

class AfterResponseYieldApplier implements ControllerYieldApplier, EventSubscriberInterface
{
    /** @var array<callable> */
    private array $tasks = [];

    /**
     * @param mixed $yield
     */
    public function supports($yield): bool
    {
        return $yield instanceof AfterResponseTask;
    }

    /**
     * @param mixed $yield
     */
    public function apply($yield, Request $request, Response $response): void
    {
        assert(is_callable($yield));

        $this->tasks[] = $yield;
    }

    public function onKernelTerminate(TerminateEvent $event): void
    {
        foreach ($this->tasks as $task) {
            $task();
        }
    }

    /**
     * @psalm-return array<string, string|array{0: string, 1: int}|list<array{0: string, 1?: int}>>
     */
    public static function getSubscribedEvents()
    {
        return ['kernel.terminate' => 'onKernelTerminate'];
    }
}
