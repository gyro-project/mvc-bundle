<?php

namespace Gyro\Bundle\MVCBundle\Tests\Controller\ResultConverter;

use Gyro\Bundle\MVCBundle\Controller\ResultConverter\HeadersYieldApplier;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Gyro\MVC\Headers;

class HeadersYieldApplierTest extends TestCase
{
    private $applier;

    public function setUp() : void
    {
        $this->applier = new HeadersYieldApplier();
    }

    public function testSupportsOnlyHeaders() : void
    {
        $this->assertTrue($this->applier->supports(new Headers(['foo' => 'bar'])));
        $this->assertFalse($this->applier->supports(new \stdClass()));
    }

    public function testApplySetsHeaders() : void
    {
        $request = new Request();
        $response = new Response();

        $this->applier->apply(new Headers(['foo' => 'bar']), $request, $response);

        $this->assertEquals('bar', $response->headers->get('foo'));
    }
}
