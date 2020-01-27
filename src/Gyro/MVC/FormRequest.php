<?php

namespace Gyro\MVC;

interface FormRequest
{
    /**
     * Attempt to handle a form and return true when handled and data is valid.
     *
     * @param string|Typeinterface       $formType
     * @param array<string,mixed>|object $bindData
     * @param array<string,mixed>        $options
     *
     * @throws Exception\FormAlreadyHandledException when a form was already bound on this request before.
     */
    public function handle($formType, $bindData = null, array $options = []) : bool;

    /**
     * Use this to retrieve the validated data from the form even when you attached `$bindData`.
     *
     * Only by using this method you can mock the form handling by providing a replacement valid value in tests.
     *
     * @return mixed
     */
    public function getValidData();

    /**
     * Is the bound form valid?
     */
    public function isValid() : bool;

    /**
     * Is the request bound to a form?
     */
    public function isBound() : bool;

    public function getForm() : \Symfony\Component\Form\FormInterface;

    /**
     * Create the form view for the handled form.
     *
     * Throws exception when no form was handled yet.
     */
    public function createFormView() : \Symfony\Component\Form\FormViewInterface;
}
