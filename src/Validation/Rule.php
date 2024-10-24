<?php

namespace SMFramework\Validation;

use SMFramework\Validation\Contracts\ValidationRuleContract;
use SMFramework\Validation\Exceptions\RuleParseException;
use SMFramework\Validation\Exceptions\UnknownRuleException;
use SMFramework\Validation\Rules\Email;
use SMFramework\Validation\Rules\LessThan;
use SMFramework\Validation\Rules\Number;
use SMFramework\Validation\Rules\Required;
use SMFramework\Validation\Rules\RequiredWhen;
use SMFramework\Validation\Rules\RequiredWith;

class Rule
{
    private static array $rules = [];
    private static array $defaultRules = [
        Required::class,
        RequiredWith::class,
        RequiredWhen::class,
        Number::class,
        LessThan::class,
        Email::class
    ];
    public static function loadDefaultRules()
    {
        self::load(self::$defaultRules);
    }
    public static function load(array $rules)
    {
        foreach($rules as $class) {
            $className = array_slice(explode("\\", $class), -1)[0];
            $ruleName = snakeCase($className);
            self::$rules[$ruleName] = $class;
        }
    }
    public static function nameOf(ValidationRuleContract $rule)
    {
        $class = new \ReflectionClass($rule);

        return snakeCase($class->getShortName());
    }
    public static function parseBasicRule(string $ruleName): ValidationRuleContract
    {
        $class = new \ReflectionClass(self::$rules[$ruleName]);

        if (count($class->getConstructor()?->getParameters() ?? []) > 0) {
            throw new RuleParseException("Rule $ruleName requires parameters, but none have been passed");
        }
        return $class->newInstance();
    }
    public static function parseRuleWithParameters(string $ruleName, string $params): ValidationRuleContract
    {
        $class = new \ReflectionClass(self::$rules[$ruleName]);
        $constructorParameters = $class->getConstructor()?->getParameters() ?? [];
        $givenParameters = array_filter(explode(",", $params), fn ($p) => !empty($p));
        if (count($givenParameters) !== count($constructorParameters)) {
            throw new RuleParseException(sprintf(
                "Rule %s requires %d parameters, but %d where given: %s",
                $ruleName,
                count($constructorParameters),
                count($givenParameters),
                $params
            ));
        }
        return $class->newInstance(...$givenParameters);
    }
    public static function from(string $str)
    {
        if (strlen($str) == 0) {
            throw new RuleParseException("Can't parse empty string to rule");
        }
        $ruleParts = explode(":", $str);
        if (!array_key_exists($ruleParts[0], self::$rules)) {
            throw new UnknownRuleException("Rule {$ruleParts[0]} not found");
        }
        if (count($ruleParts) == 1) {
            return self::parseBasicRule($ruleParts[0]);
        }
        [$ruleName, $params] = $ruleParts;
        return self::parseRuleWithParameters($ruleName, $params);
    }

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
}
