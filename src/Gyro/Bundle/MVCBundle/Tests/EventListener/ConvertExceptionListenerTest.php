<?php

namespace Gyro\Bundle\MVCBundle\Tests\EventListener;

use PHPUnit\Framework\TestCase;
use Gyro\Bundle\MVCBundle\EventListener\ConvertExceptionListener;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use OutOfBoundsException;

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
            $event = new GetResponseForExceptionEvent(
                \Phake::mock('Symfony\Component\HttpKernel\KernelInterface'),
                \Phake::mock('Symfony\Component\HttpFoundation\Request'),
                0,
                $original = new OutOfBoundsException()
            )
        );

        $this->assertInstanceOf('Symfony\Component\HttpKernel\Exception\NotFoundHttpException', $event->getException());
        $this->assertSame($original, $event->getException()->getPrevious());
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
            $event = new GetResponseForExceptionEvent(
                \Phake::mock('Symfony\Component\HttpKernel\KernelInterface'),
                \Phake::mock('Symfony\Component\HttpFoundation\Request'),
                0,
                new OutOfBoundsException()
            )
        );

        $this->assertInstanceOf('Symfony\Component\HttpKernel\Exception\NotFoundHttpException', $event->getException());
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
            $event = new GetResponseForExceptionEvent(
                \Phake::mock('Symfony\Component\HttpKernel\KernelInterface'),
                \Phake::mock('Symfony\Component\HttpFoundation\Request'),
                0,
                $original = new OutOfBoundsException()
            )
        );

        $this->assertInstanceOf('Symfony\Component\HttpKernel\Exception\HttpException', $event->getException());
        $this->assertEquals(405, $event->getException()->getStatusCode());
        $this->assertSame($original, $event->getException()->getPrevious());
    }
}
