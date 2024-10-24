<?php

namespace SMFramework\Validation\Contracts;

interface ValidationRuleContract
{
    public function message(): string;
    public function isValid(string $field, array $data): bool;
}
