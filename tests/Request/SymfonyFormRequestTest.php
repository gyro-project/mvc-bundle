<?php

namespace Gyro\Bundle\MVCBundle\Tests\Request;

use PHPUnit\Framework\TestCase;
use Gyro\Bundle\MVCBundle\Request\SymfonyFormRequest;
use Symfony\Component\HttpFoundation\Request;

class SymfonyFormRequestTest extends TestCase
{
    /**
     * @test
     */
    public function it_handles_form_request() : void
    {
        $formRequest = new SymfonyFormRequest(
            $request = new Request(),
            $formFactory = \Phake::mock('Symfony\Component\Form\FormFactoryInterface')
        );

        \Phake::when($formFactory)->create('form_type', null, [])->thenReturn(
            $form = \Phake::mock('Symfony\Component\Form\Form')
        );
        \Phake::when($form)->isSubmitted()->thenReturn(true);
        \Phake::when($form)->isValid()->thenReturn(true);
        \Phake::when($form)->handleRequest($request)->thenReturn($form);

        $handled = $formRequest->handle('form_type');

        \Phake::verify($form)->handleRequest($request);

        $this->assertTrue($handled);
    }

    /**
     * @test
     */
    public function it_allows_handle_only_once() : void
    {
        $formRequest = new SymfonyFormRequest(
            $request = new Request(),
            $formFactory = \Phake::mock('Symfony\Component\Form\FormFactoryInterface')
        );

        \Phake::when($formFactory)->create('form_type', null, [])->thenReturn(
            $form = \Phake::mock('Symfony\Component\Form\Form')
        );
        \Phake::when($form)->handleRequest($request)->thenReturn($form);

        $formRequest->handle('form_type');

        $this->expectException('Gyro\MVC\Exception\FormAlreadyHandledException');
        $formRequest->handle('form_type');
    }

    /**
     * @test
     */
    public function it_requires_handle_before_create_view() : void
    {
        $formRequest = new SymfonyFormRequest(
            $request = new Request(),
            $formFactory = \Phake::mock('Symfony\Component\Form\FormFactoryInterface')
        );

        $this->expectException('Gyro\MVC\Exception\NoFormHandledException');
        $formRequest->createFormView();
    }
}
