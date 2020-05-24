<?php

namespace Gyro\Bundle\MVCBundle\Tests\EventListener;

use PackageVersions\Versions;
use PHPUnit\Framework\TestCase;
use Gyro\Bundle\MVCBundle\EventListener\ParamConverterListener;
use Gyro\Bundle\MVCBundle\ParamConverter\SymfonyServiceProvider;
use Gyro\MVC\TokenContext;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

function iAmAController(TokenContext $context) : void
{
}

class ParamConverterListenerTest extends TestCase
{
    private $kernel;

    private $listener;

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
        $container = new Container();
        $container->set(
            'security.token_storage',
            \Phake::mock(TokenStorageInterface::class)
        );
        $container->set(
            'security.authorization_checker',
            \Phake::mock(AuthorizationCheckerInterface::class)
        );
        $serviceProvider = new SymfonyServiceProvider($container);

        $this->kernel   = \Phake::mock(HttpKernelInterface::class);
        $this->listener = new ParamConverterListener($serviceProvider);
    }

    public function createControllerEvent(callable $method, Request $request): object
    {
        if (version_compare('4.4.0', Versions::getVersion('symfony/symfony'), '<=')) {
            return new ControllerEvent($this->kernel, $method, $request, HttpKernelInterface::MASTER_REQUEST);
        }

        return new FilterControllerEvent($this->kernel, $method, $request, HttpKernelInterface::MASTER_REQUEST);
    }
}
