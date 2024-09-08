<?php

namespace OtherPHPFramework\Validation;

use OtherPHPFramework\Validation\Contracts\ValidationRuleContract;
use OtherPHPFramework\Validation\Rules\Email;
use OtherPHPFramework\Validation\Rules\LessThan;
use OtherPHPFramework\Validation\Rules\Number;
use OtherPHPFramework\Validation\Rules\Required;
use OtherPHPFramework\Validation\Rules\RequiredWhen;
use OtherPHPFramework\Validation\Rules\RequiredWith;

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
    public static function lessThan(int|float $value): ValidationRuleContract
    {
        return new LessThan($value);
    }
    public static function requiredWhen(
        string $otherField,
        string $operator,
        int|float $value
    ): ValidationRuleContract {
        return new RequiredWhen($otherField, $operator, $value);
    }
    public static function from(string $str)
    {

    }
}
