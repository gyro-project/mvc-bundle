<?php

namespace Gyro\Bundle\NoFrameworkBundle\Tests;

use PHPUnit\Framework\TestCase;
use Gyro\Bundle\NoFrameworkBundle\MockTokenContext;

class MockTokenContextTest extends TestCase
{
    /**
     * @test
     */
    public function it_grants_access_from_token_roles()
    {
        $user = \Phake::mock('Symfony\Component\Security\Core\User\UserInterface');
        \Phake::when($user)->getRoles()->thenReturn(array('ROLE_USER', 'ROLE_ADMIN'));

        $context = new MockTokenContext($user);

        $this->assertTrue($context->isGranted('ROLE_USER'));
    }
}
