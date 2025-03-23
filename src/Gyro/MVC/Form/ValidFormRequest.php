<?php

namespace Gyro\MVC\Form;

use Gyro\MVC\FormRequest;
use Symfony\Component\Form\FormInterface;
use BadMethodCallException;
use Symfony\Component\Form\FormView;
use Gyro\MVC\Exception\FormAlreadyHandledException;

class ValidFormRequest implements FormRequest
{
    /**
     * @param array<string,mixed>|object $validData
     */
    public function __construct(private $validData)
    {
    }

    /**
     * Attempt to handle a form and return true when handled and data is valid.
     *
     * @param array<string,mixed>|object $bindData
     * @param array<string,mixed>        $options
     *
     * @throws FormAlreadyHandledException when a form was already bound on this request before.
     */
    public function handle(string $formType, $bindData = null, array $options = []): bool
    {
        return true;
    }

    /**
     * Use this to retrieve the validated data from the form even when you attached `$bindData`.
     *
     * Only by using this method you can mock the form handling by providing a replacement valid value in tests.
     *
     * @return array<string,mixed>|object
     */
    public function getValidData()
    {
        return $this->validData;
    }

    /**
     * Is the bound form valid?
     */
    public function isValid(): bool
    {
        return true;
    }

    /**
     * Is the request bound to a form?
     */
    public function isBound(): bool
    {
        return true;
    }

    public function getForm(): FormInterface
    {
        throw new BadMethodCallException("Not supported in ValidFormRequest");
    }

    /**
     * Create the form view for the handled form.
     *
     * Throws exception when no form was handled yet.
     */
    public function createFormView(): FormView
    {
        return new FormView();
    }
}
