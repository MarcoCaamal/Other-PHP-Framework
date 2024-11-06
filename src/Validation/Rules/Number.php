<?php

namespace LightWeight\Validation\Rules;

use LightWeight\Validation\Contracts\ValidationRuleContract;

class Number implements ValidationRuleContract
{
    /**
     *
     * @param string $field
     * @param array $data
     * @return bool
     */
    public function isValid(string $field, array $data): bool
    {
        return isset($data[$field])
            && is_numeric($data[$field]);
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return "Must be number";
    }
}
