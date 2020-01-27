<?php

namespace Gyro\Bundle\MVCBundle\Tests;

use PHPUnit\Framework\TestCase;
use Gyro\Bundle\MVCBundle\SymfonyTokenContext;

class SymfonyTokenContextTest extends TestCase
{
    private $tokenStorage;
    private $authorizationChecker;

    public function setUp() : void
    {
        $this->tokenStorage = \Phake::mock('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface');
        $this->authorizationChecker = \Phake::mock('Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface');
        parent::setUp();
    }

    /**
     * @test
     */
    public function it_retrieves_token_from_security_context() : void
    {
        $token = \Phake::mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        \Phake::when($this->tokenStorage)->getToken()->thenReturn($token);

        $context = new SymfonyTokenContext($this->tokenStorage, $this->authorizationChecker);

        $this->assertTrue($context->hasToken());
        $this->assertSame($token, $context->getToken());
    }

    /**
     * @test
     */
    public function it_throws_unauthenticated_user_exception_when_no_token() : void
    {
        $context = new SymfonyTokenContext($this->tokenStorage, $this->authorizationChecker);

        $this->expectException('Gyro\MVC\Exception\UnauthenticatedUserException');

        $context->getToken();
    }

    /**
     * @test
     */
    public function it_allows_check_has_token() : void
    {
        $context = new SymfonyTokenContext($this->tokenStorage, $this->authorizationChecker);

        $this->assertFalse($context->hasToken());
    }
}
