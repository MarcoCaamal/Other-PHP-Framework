<?php

namespace LightWeight\Validation\Exceptions;

use LightWeight\Exceptions\LightWeightException;

class ValidationException extends LightWeightException
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
