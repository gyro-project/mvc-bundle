<?php

namespace Gyro\Bundle\MVCBundle\Tests\EventListener;

use Gyro\Bundle\MVCBundle\Versions;
use PHPUnit\Framework\TestCase;
use Gyro\Bundle\MVCBundle\EventListener\ParamConverterListener;
use Gyro\Bundle\MVCBundle\ParamConverter\SymfonyServiceProvider;
use Gyro\MVC\TokenContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

function iAmAController(TokenContext $context) : void
{
}

#[PHP8] function iAmAFunctionWithAUnionTypeParameter(Session|TokenContext $context): void {}

class ParamConverterListenerTest extends TestCase
{
    private $kernel;

    private \Gyro\Bundle\MVCBundle\EventListener\ParamConverterListener $listener;

    /**
     * @test
     */
    public function it_converts_parameters() : void
    {
        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));

        $method = function (Session $session, TokenContext $context) : void {
        };
        $event = $this->createControllerEvent($method, $request);

        $this->listener->onKernelController($event);

        $this->assertTrue($request->attributes->has('context'));
        $this->assertTrue($request->attributes->has('session'));
    }

    /**
     * @test
     */
    public function it_skips_union_types() : void
    {
        if (version_compare(phpversion(), '8.0') < 0) {
            self::markTestSkipped('Union types are only available for PHP>=8.0');
        }

        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));

        $event = $this->createControllerEvent('Gyro\Bundle\MVCBundle\Tests\EventListener\iAmAFunctionWithAUnionTypeParameter', $request);

        $this->listener->onKernelController($event);

        $this->assertFalse($request->attributes->has('context'));
    }

    /**
     * @test
     */
    public function it_supports_array_contollers() : void
    {
        $controller = new class()
        {
            public function action(TokenContext $context) : void
            {
            }
        };
        $controller = [$controller, 'action'];

        $this->assertRequestHasContext($controller);
    }

    /**
     * @param mixed $controller
     */
    private function assertRequestHasContext($controller) : void
    {
        $request = new Request();
        $event   = $this->createControllerEvent($controller, $request);

        $this->listener->onKernelController($event);

        $this->assertTrue($request->attributes->has('context'));
    }

    /**
     * @test
     */
    public function it_supports_invokable_controllers() : void
    {
        $controller = new class()
        {
            public function __invoke(TokenContext $context) : void
            {
            }
        };

        $this->assertRequestHasContext($controller);
    }

    /**
     * @test
     */
    public function it_supports_callable_names() : void
    {
        $this->assertRequestHasContext('\Gyro\Bundle\MVCBundle\Tests\EventListener\iAmAController');
    }

    /**
     * @return array|void
     */
    public function setUp() : void
    {
        $serviceProvider = new SymfonyServiceProvider(
            null,
            \Phake::mock(TokenStorageInterface::class),
            \Phake::mock(AuthorizationCheckerInterface::class),
        );

        $this->kernel   = \Phake::mock(HttpKernelInterface::class);
        $this->listener = new ParamConverterListener($serviceProvider);
    }

    public function createControllerEvent(callable $method, Request $request): object
    {
        return new ControllerEvent($this->kernel, $method, $request,Versions::hasMainRequestConstant()
            ? HttpKernelInterface::MAIN_REQUEST
            : HttpKernelInterface::MASTER_REQUEST
        );
    }
}
