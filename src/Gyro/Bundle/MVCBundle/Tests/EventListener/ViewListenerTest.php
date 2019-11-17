<?php

namespace Gyro\Bundle\MVCBundle\Tests\EventListener;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

use Gyro\Bundle\MVCBundle\Controller\ResultConverter\ControllerResultConverter;
use Gyro\Bundle\MVCBundle\Controller\ResultConverter\ControllerYieldApplier;
use Gyro\Bundle\MVCBundle\EventListener\ViewListener;
use Gyro\MVC\TemplateView;
use Gyro\MVC\RedirectRoute;

class ViewListenerTest extends TestCase
{
    const A_CONTROLLER = 'foo';
    const A_TEMPLATE = 'bar';
    const A_TEMPLATE_OVERWRITE = 'baz';

    private $listener;
    private $converter;
    private $applier;

    public function setUp() : void
    {
        $this->converter = \Phake::mock(ControllerResultConverter::class);
        $this->applier = \Phake::mock(ControllerYieldApplier::class);

        $this->listener = new ViewListener();
        $this->listener->addConverter($this->converter);
        $this->listener->addYieldApplier($this->applier);
    }

    /**
     * @test
     */
    public function it_ignores_requests_without_controller()
    {
        $request = $this->requestForController(null);

        $this->listener->onKernelView($this->createEventWith($request));

        \Phake::verifyNoInteraction($this->converter);
    }

    /**
     * @test
     */
    public function it_ignores_requests_returning_valid_response()
    {
        $result = new Response();

        $request = $this->requestForController(self::A_CONTROLLER);

        $this->listener->onKernelView($this->createEventWith($request, $result));

        \Phake::verifyNoInteraction($this->converter);
    }

    /**
     * @test
     */
    public function it_generates_response_with_controller_array_result()
    {
        $result = array('foo' => 'bar');

        $request = $this->requestForController(self::A_CONTROLLER);

        $this->expectConverterToSupportResult();

        $this->listener->onKernelView($event = $this->createEventWith($request, $result));

        \Phake::verify($this->converter)->convert(['foo' => 'bar'], $request, null);

        $this->assertInstanceOf(Response::class, $event->getResponse());
    }

    /**
     * @test
     */
    public function it_generates_response_with_controller_generator_result()
    {
        $r = new RedirectRoute('foo');
        $t = new TemplateView('foo');
        $ctrl = function() use ($r, $t) {
            yield $r;
            yield $t;
            return ['foo' => 'bar'];
        };
        $result = $ctrl();

        $request = $this->requestForController(self::A_CONTROLLER);

        $this->expectConverterToSupportResult();

        $this->listener->onKernelView($event = $this->createEventWith($request, $result));

        \Phake::verify($this->converter)->convert(['foo' => 'bar'], $request);
        \Phake::verify($this->applier)->supports($r);
        \Phake::verify($this->applier)->supports($t);

        $this->assertInstanceOf(Response::class, $event->getResponse());
    }

    private function requestForController($controller)
    {
        $request = Request::create('GET', '/');
        $request->attributes->set('_controller', $controller);

        return $request;
    }

    private function expectConverterToSupportResult()
    {
        \Phake::when($this->converter)->supports(\Phake::anyParameters())->thenReturn(true);
    }

    private function createEventWith(Request $request, $controllerResult = null)
    {
        return new GetResponseForControllerResultEvent(
            \Phake::mock('Symfony\Component\HttpKernel\HttpKernelInterface'),
            $request,
            HttpKernelInterface::MASTER_REQUEST,
            $controllerResult
        );
    }
}
