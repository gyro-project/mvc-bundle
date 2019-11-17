<?php

namespace Gyro\Bundle\NoFrameworkBundle\Tests\Request;

use PHPUnit\Framework\TestCase;
use Gyro\Bundle\NoFrameworkBundle\Request\SymfonyFormRequest;
use Symfony\Component\HttpFoundation\Request;

class SymfonyFormRequestTest extends TestCase
{
    /**
     * @test
     */
    public function it_handles_form_request()
    {
        $formRequest = new SymfonyFormRequest(
            $request = new Request(),
            $formFactory = \Phake::mock('Symfony\Component\Form\FormFactoryInterface')
        );

        \Phake::when($formFactory)->create('form_type', null, array())->thenReturn(
            $form = \Phake::mock('Symfony\Component\Form\Form')
        );
        \Phake::when($form)->isSubmitted()->thenReturn(true);
        \Phake::when($form)->isValid()->thenReturn(true);

        $handled = $formRequest->handle('form_type');

        \Phake::verify($form)->handleRequest($request);

        $this->assertTrue($handled);
    }

    /**
     * @test
     */
    public function it_allows_handle_only_once()
    {
        $formRequest = new SymfonyFormRequest(
            $request = new Request(),
            $formFactory = \Phake::mock('Symfony\Component\Form\FormFactoryInterface')
        );

        \Phake::when($formFactory)->create('form_type', null, array())->thenReturn(
            $form = \Phake::mock('Symfony\Component\Form\Form')
        );

        $formRequest->handle('form_type');

        $this->expectException('Gyro\MVC\Exception\FormAlreadyHandledException');
        $formRequest->handle('form_type');
    }

    /**
     * @test
     */
    public function it_requires_handle_before_create_view()
    {
        $formRequest = new SymfonyFormRequest(
            $request = new Request(),
            $formFactory = \Phake::mock('Symfony\Component\Form\FormFactoryInterface')
        );

        $this->expectException('Gyro\MVC\Exception\NoFormHandledException');
        $formRequest->createFormView();
    }
}
