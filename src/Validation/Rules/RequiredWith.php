<?php

namespace OtherPHPFramework\Validation\Rules;

use OtherPHPFramework\Validation\Contracts\ValidationRuleContract;

class RequiredWith implements ValidationRuleContract
{
    protected string $withField;
    public function __construct(string $withField)
    {
        $this->withField = $withField;
    }
    /**
     *
     * @param string $field
     * @param array $data
     * @return bool
     */
    public function isValid(string $field, array $data): bool
    {
        if (isset($data[$this->withField]) && $data[$this->withField] != "") {
            return isset($data[$field]) && $data[$field] != "";
        }

        return true;
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return "This field  is required when {$this->withField} is present";
    }
}
