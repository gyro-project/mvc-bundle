<?php

namespace Controller\ResultConverter;

use Gyro\Bundle\MVCBundle\Controller\ResultConverter\AfterResponseYieldApplier;
use Gyro\MVC\AfterResponseTask;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class AfterResponseTaskYieldApplierTest extends TestCase
{
    private bool $taskExecuted = false;

    public function testEndToEnd(): void
    {
        $request = new Request();
        $response = new Response();
        $kernel = \Phake::mock(HttpKernelInterface::class);

        $yieldApplier = new AfterResponseYieldApplier();
        $task = new AfterResponseTask(fn () => $this->taskExecuted = true);

        $this->assertTrue($yieldApplier->supports($task));

        $yieldApplier->apply($task, $request, $response);

        $this->assertFalse($this->taskExecuted);

        $yieldApplier->onKernelTerminate(new TerminateEvent($kernel, $request, $response));

        $this->assertTrue($this->taskExecuted);
    }
}
