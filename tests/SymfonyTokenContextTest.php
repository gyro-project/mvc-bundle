<?php

namespace Gyro\Bundle\MVCBundle\Tests;

use Gyro\MVC\Exception\UnauthenticatedUserException;
use PHPUnit\Framework\TestCase;
use Gyro\Bundle\MVCBundle\SymfonyTokenContext;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class SymfonyTokenContextTest extends TestCase
{
    private $tokenStorage;
    private $authorizationChecker;

    public function setUp() : void
    {
        $this->tokenStorage = \Phake::mock(TokenStorageInterface::class);
        $this->authorizationChecker = \Phake::mock(AuthorizationChecker::class);

        parent::setUp();
    }

    /**
     * @test
     */
    public function it_retrieves_token_from_security_context() : void
    {
        $token = \Phake::mock(TokenInterface::class);
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

        $this->expectException(UnauthenticatedUserException::class);

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
