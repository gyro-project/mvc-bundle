<?php

namespace Gyro\MVC\Exception;

use RuntimeException;

class FormAlreadyHandledException extends RuntimeException
{
    public function __construct(string $name)
    {
        parent::__construct(sprintf(
            'The \Gyro\MVC\FormRequest was already handled with form %s earlier. ' .
            'You can only use a FormRequest with exactly one form.',
            $name
        ));
    }
}
