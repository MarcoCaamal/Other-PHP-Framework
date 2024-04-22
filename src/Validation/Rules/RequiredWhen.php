<?php

namespace Junk\Validation\Rules;

use Junk\Validation\Contracts\ValidationRuleContract;

class RequiredWhen implements ValidationRuleContract
{
    public function __construct(
        private string $otherField,
        private string $operator,
        private string $compareWith
    )
    {
        $this->otherField = $otherField;
        $this->operator = $operator;
        $this->compareWith = $compareWith;
    }
    /**
     *
     * @param string $field
     * @param array $data
     * @return bool
     */
    public function isValid(string $field, array $data): bool
    {
        if(!array_key_exists($this->otherField, $data)) {
            return false;
        }

        $isRequired = match($this->operator) {
            "="  => $data[$this->otherField] == $this->compareWith,
            ">"  => $data[$this->otherField] >  floatval($this->compareWith),
            "<"  => $data[$this->otherField] <  floatval($this->compareWith),
            ">=" => $data[$this->otherField] >= floatval($this->compareWith),
            "<=" => $data[$this->otherField] <= floatval($this->compareWith),
        };

        return !$isRequired || isset($data[$field]) && $data[$field] != "";
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return "This field is required when $this->otherField $this->operator $this->compareWith";
    }
}
