<?php

namespace LightWeight\Validation\Rules;

use LightWeight\Validation\Contracts\ValidationRuleContract;

class IsString implements ValidationRuleContract
{
    /**
     *
     * @param string $field
     * @param array $data
     * @return bool
     */
    public function isValid(string $field, array $data): bool
    {
        return isset($data[$field]) && is_string($data[$field]);
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return "Field is required";
    }
}
