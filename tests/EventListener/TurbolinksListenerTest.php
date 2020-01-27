<?php

namespace Gyro\Bundle\MVCBundle\Tests\EventListener;

use PHPUnit\Framework\TestCase;
use Gyro\Bundle\MVCBundle\EventListener\TurbolinksListener;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class TurbolinksListenerTest extends TestCase
{
    private $listener;

    public function setUp() : void
    {
        $this->listener = new TurbolinksListener();
    }

    /**
     * @test
     */
    public function it_stores_turbolink_location() : void
    {
        $event = $this->createEventWith($response = new RedirectResponse('/two'));
        $this->listener->onKernelResponse($event);

        $session = $event->getRequest()->getSession();

        \Phake::verify($session)->set('turbolinks_location', '/two');
    }

    /**
     * @test
     */
    public function it_passes_turbolinks_location_to_response() : void
    {
        $event = $this->createEventWith($response = new Response());

        $session = $event->getRequest()->getSession();
        \Phake::when($session)->has('turbolinks_location')->thenReturn(true);
        \Phake::when($session)->get('turbolinks_location')->thenReturn('/two');

        $this->listener->onKernelResponse($event);

        $this->assertEquals('/two', $response->headers->get('Turbolinks-Location'));
    }

    private function createEventWith(Response $response)
    {
        $request = Request::create('GET', '/');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $request->headers->set('Turbolinks-Referrer', '/');
        $request->setSession(\Phake::mock(SessionInterface::class));

        return new FilterResponseEvent(
            \Phake::mock('Symfony\Component\HttpKernel\HttpKernelInterface'),
            $request,
            HttpKernelInterface::MASTER_REQUEST,
            $response
        );
    }
}
