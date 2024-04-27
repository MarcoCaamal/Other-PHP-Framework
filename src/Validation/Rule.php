<?php

namespace Junk\Validation;

use Junk\Validation\Contracts\ValidationRuleContract;
use Junk\Validation\Rules\Email;
use Junk\Validation\Rules\LessThan;
use Junk\Validation\Rules\Number;
use Junk\Validation\Rules\Required;
use Junk\Validation\Rules\RequiredWhen;
use Junk\Validation\Rules\RequiredWith;

class Rule
{
    public static function email(): ValidationRuleContract
    {
        return new Email();
    }
    public static function required(): ValidationRuleContract
    {
        return new Required();
    }
    public static function requiredWith(string $withField): ValidationRuleContract
    {
        return new RequiredWith($withField);
    }
    public static function number(): ValidationRuleContract
    {
        return new Number();
    }
    public static function lessThan(int|float $value) : ValidationRuleContract
    {
        return new LessThan($value);
    }
    public static function requiredWhen(
        string $otherField,
        string $operator,
        int|float $value
    ) : ValidationRuleContract
    {
        return new RequiredWhen($otherField, $operator, $value);
    }
}
