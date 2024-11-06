<?php

namespace LightWeight\Validation\Rules;

use LightWeight\Validation\Contracts\ValidationRuleContract;

class LessThan implements ValidationRuleContract
{
    public function __construct(private float $lessThan)
    {
        $this->lessThan = $lessThan;
    }
    /**
     *
     * @param string $field
     * @param array $data
     * @return bool
     */
    public function isValid(string $field, array $data): bool
    {
        return isset($data[$field])
        && is_numeric($data[$field])
        && $data[$field] < $this->lessThan;
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return 'Must be numeric value less than 5';
    }
}
