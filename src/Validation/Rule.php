<?php

namespace Junk\Validation;

use Junk\Validation\Contracts\ValidationRuleContract;
use Junk\Validation\Rules\Email;
use Junk\Validation\Rules\Required;
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
}
