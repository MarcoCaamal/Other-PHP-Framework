<?php

namespace SMFramework\Validation\Exceptions;

use SMFramework\Exceptions\SMFrameworkException;

class ValidationException extends SMFrameworkException
{
    public function __construct(protected array $errors)
    {
        $this->errors = $errors;
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
