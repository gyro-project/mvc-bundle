<?php

namespace Gyro\Bundle\MVCBundle\Controller\ResultConverter;

use Gyro\MVC\AfterResponseTask;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

class AfterResponseYieldApplier implements ControllerYieldApplier, EventSubscriberInterface
{
    private $tasks = [];

    public function supports($yield): bool
    {
        return $yield instanceof AfterResponseTask;
    }

    public function apply($yield, Request $request, Response $response): void
    {
        $this->tasks[] = $yield;
    }

    public function onKernelTerminate(TerminateEvent $event): void
    {
        foreach ($this->tasks as $task) {
            $task();
        }
    }

    public static function getSubscribedEvents()
    {
        return ['kernel.terminate' => 'onKernelTerminate'];
    }
}
