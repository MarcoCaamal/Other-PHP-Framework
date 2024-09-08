<?php

namespace OtherPHPFramework\Validation\Rules;

use OtherPHPFramework\Validation\Contracts\ValidationRuleContract;

class Required implements ValidationRuleContract
{
    /**
     *
     * @param string $field
     * @param array $data
     * @return bool
     */
    public function isValid(string $field, array $data): bool
    {
        return isset($data[$field]) && $data[$field] != "";
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return "This field is required";
    }
}
