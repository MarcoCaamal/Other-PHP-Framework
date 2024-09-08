<?php

namespace Junk\Validation\Exceptions;

use Junk\Exceptions\JunkException;

class ValidationException extends JunkException
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
