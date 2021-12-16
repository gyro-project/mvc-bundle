<?php

namespace Gyro\Bundle\MVCBundle\Tests\EventListener;

use PackageVersions\Versions;
use PHPUnit\Framework\TestCase;
use Gyro\Bundle\MVCBundle\EventListener\ConvertExceptionListener;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use OutOfBoundsException;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class ConvertExceptionListenerTest extends TestCase
{
    /**
     * @test
     */
    public function it_converts_exceptions() : void
    {
        $logger = \Phake::mock('Psr\Log\LoggerInterface');
        $listener = new ConvertExceptionListener(
            $logger,
            ['OutOfBoundsException' => 'Symfony\Component\HttpKernel\Exception\NotFoundHttpException']
        );
        $listener->onKernelException(
            $event = $this->createExceptionEvent($original = new OutOfBoundsException())
        );

        $this->assertInstanceOf('Symfony\Component\HttpKernel\Exception\NotFoundHttpException', $event->getThrowable());
        $this->assertSame($original, $event->getThrowable()->getPrevious());
    }

    /**
     * @test
     */
    public function it_matches_subclasses_when_converting() : void
    {
        $logger = \Phake::mock('Psr\Log\LoggerInterface');
        $listener = new ConvertExceptionListener(
            $logger,
            ['Exception' => 'Symfony\Component\HttpKernel\Exception\NotFoundHttpException']
        );
        $listener->onKernelException(
            $event = $this->createExceptionEvent($original = new OutOfBoundsException())
        );

        $this->assertInstanceOf('Symfony\Component\HttpKernel\Exception\NotFoundHttpException', $event->getThrowable());
    }

    /**
     * @test
     */
    public function it_converts_numbers_to_http_status_code_exception() : void
    {
        $logger = \Phake::mock('Psr\Log\LoggerInterface');
        $listener = new ConvertExceptionListener(
            $logger,
            ['OutOfBoundsException' => 405]
        );
        $listener->onKernelException(
            $event = $this->createExceptionEvent($original = new OutOfBoundsException())
        );

        $this->assertInstanceOf('Symfony\Component\HttpKernel\Exception\HttpException', $event->getThrowable());
        $this->assertEquals(405, $event->getThrowable()->getStatusCode());
        $this->assertSame($original, $event->getThrowable()->getPrevious());
    }

    /**
     * @return ExceptionEvent
     */
    public function createExceptionEvent(\Throwable $original): object
    {
        return new ExceptionEvent(
            \Phake::mock('Symfony\Component\HttpKernel\KernelInterface'),
            \Phake::mock('Symfony\Component\HttpFoundation\Request'),
            0,
            $original
        );
    }
}
