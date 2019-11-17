<?php

namespace Gyro\Bundle\MVCBundle\Tests\Controller\ResultConverter;

use Gyro\Bundle\MVCBundle\Controller\ResultConverter\CookieYieldApplier;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;

class CookieYieldApplierTest extends TestCase
{
    private $applier;

    public function setUp() : void
    {
        $this->applier = new CookieYieldApplier();
    }

    public function testSupportsOnlyCookie()
    {
        $this->assertTrue($this->applier->supports(new Cookie('foo', 'bar')));
        $this->assertFalse($this->applier->supports(new \stdClass));
    }

    public function testApplySetsCookie()
    {
        $request = new Request();
        $response = new Response();

        $this->applier->apply(new Cookie('foo', 'bar'), $request, $response);

        $this->assertEquals('foo=bar; path=/; httponly', $response->headers->get('set-cookie'));
    }
}
