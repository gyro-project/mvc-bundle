<?php

namespace Gyro\Bundle\MVCBundle\Tests\Controller\ResultConverter;

use Gyro\Bundle\MVCBundle\Controller\ResultConverter\FlashYieldApplier;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Gyro\MVC\Flash;

class FlashYieldApplierTest extends TestCase
{
    private \Gyro\Bundle\MVCBundle\Controller\ResultConverter\FlashYieldApplier $applier;

    public function setUp() : void
    {
        $this->applier = new FlashYieldApplier();
    }

    public function testSupportsOnlyFlash() : void
    {
        $this->assertTrue($this->applier->supports(new Flash('foo', 'bar')));
        $this->assertFalse($this->applier->supports(new \stdClass()));
    }

    public function testApplySetsFlash() : void
    {
        $request = new Request();
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);
        $response = new Response();

        $this->applier->apply(new Flash('foo', 'bar'), $request, $response);

        $this->assertEquals(['bar'], $request->getSession()->getFlashBag()->get('foo'));
    }
}
