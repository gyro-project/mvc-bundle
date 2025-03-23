<?php

namespace Gyro\Bundle\MVCBundle\Request;

use Gyro\MVC\Exception;
use Gyro\MVC\FormRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class SymfonyFormRequest implements FormRequest
{
    private ?\Symfony\Component\Form\FormInterface $form = null;

    public function __construct(private Request $request, private FormFactoryInterface $formFactory)
    {
    }

    /**
     * Attempt to handle a form and return true when handled and data is valid.
     *
     * @param array<string,mixed>|object $bindData
     * @param array<string,mixed>        $options
     *
     * @throws Exception\FormAlreadyHandledException when a form was already bound on this request before.
     */
    public function handle(string $formType, $bindData = null, array $options = []): bool
    {
        if ($this->form !== null) {
            throw new Exception\FormAlreadyHandledException($this->form->getName());
        }

        $this->form = $this->formFactory->create($formType, $bindData, $options);
        $this->form->handleRequest($this->request);

        return $this->form->isSubmitted() && $this->form->isValid();
    }

    /**
     * Use this to retrieve the validated data from the form even when you attached `$bindData`.
     *
     * Only by using this method you can mock the form handling by providing a replacement valid value in tests.
     *
     * @return mixed
     */
    public function getValidData()
    {
        $this->assertFormHandled();

        return $this->form->getData();
    }

    /**
     * Is the bound form valid?
     */
    public function isValid(): bool
    {
        $this->assertFormHandled();

        return $this->form->isValid();
    }

    /**
     * Is the request bound to a form?
     */
    public function isBound(): bool
    {
        $this->assertFormHandled();

        return $this->form->isSubmitted();
    }

    public function getForm(): FormInterface
    {
        $this->assertFormHandled();

        return $this->form;
    }

    /**
     * Create the form view for the handled form.
     *
     * Throws exception when no form was handled yet.
     */
    public function createFormView(): FormView
    {
        $this->assertFormHandled();

        return $this->form->createView();
    }

    private function assertFormHandled(): void
    {
        if ($this->form === null) {
            throw new Exception\NoFormHandledException();
        }
    }
}
