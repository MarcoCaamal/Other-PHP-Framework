<?php

namespace OtherPHPFramework\Validation\Exceptions;

use OtherPHPFramework\Exceptions\OtherPHPFrameworkException;

class ValidationException extends OtherPHPFrameworkException
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
