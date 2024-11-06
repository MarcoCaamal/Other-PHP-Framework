<?php

namespace LightWeight\Tests\Validation;

use LightWeight\Validation\Exceptions\ValidationException;
use LightWeight\Validation\Rule;
use LightWeight\Validation\Rules\Email;
use LightWeight\Validation\Rules\LessThan;
use LightWeight\Validation\Rules\Number;
use LightWeight\Validation\Rules\Required;
use LightWeight\Validation\Rules\RequiredWith;
use LightWeight\Validation\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    protected function setUp(): void
    {
        Rule::loadDefaultRules();
    }
    public function test_basic_validation_passes()
    {
        $data = [
            "email" => "test@test.com",
            "other" => 2,
            "num" => 3,
            "foo" => 5,
            "bar" => 4
        ];

        $rules = [
            "email" => new Email(),
            "other" => new Required(),
            "num" => new Number(),
        ];

        $expected = [
            "email" => "test@test.com",
            "other" => 2,
            "num" => 3,
        ];

        $v = new Validator($data);

        $this->assertEquals($expected, $v->validate($rules));
    }

    public function testThrowsValidationExceptionOnInvalidData()
    {
        $this->expectException(ValidationException::class);
        $v = new Validator(["test" => "test"]);
        $v->validate(["test" => new Number()]);
    }
    public function testOverridesErrorMessagesCorrectly()
    {
        $data = ["email" => "test@", "num1" => "not a number"];
        $rules = [
            "email" => "email",
            "num1" => "number",
            "num2" =>  ["required", "number"],
        ];
        $messages = [
            "email" => ["email" => "test email message"],
            "num1" => ["number" => "test number message"],
            "num2" =>  [
                "required" => "test required message",
                "number" => "test number message again"
            ]
        ];
        $v = new Validator($data);
        try {
            $v->validate($rules, $messages);
            $this->fail("Did not throw ValidationException");
        } catch (ValidationException $e) {
            $this->assertEquals($messages, $e->errors());
        }
    }
    /**
     * @depends test_basic_validation_passes
     */
    public function testMultipleRulesValidation()
    {
        $data = ["age" => 20, "num" => 3, "foo" => 5];

        $rules = [
            "age" => new LessThan(100),
            "num" => [new RequiredWith("age"), new Number()],
        ];

        $expected = ["age" => 20, "num" => 3];

        $v = new Validator($data);

        $this->assertEquals($expected, $v->validate($rules));
    }
    public function testBasicValidatationPassesWithStrings()
    {
        $data = [
            "email" => "test@test.com",
            "other" => 2,
            "num" => 3,
            "foo" => 5,
            "bar" => 4
        ];
        $rules = [
            "email" => "email",
            "other" => "required",
            "num" => "number",
        ];
        $expected = [
            "email" => "test@test.com",
            "other" => 2,
            "num" => 3,
        ];
        $v = new Validator($data);
        $this->assertEquals($expected, $v->validate($rules));
    }
    public function testReturnsMessagesForEachRuleThatDoesntPass()
    {
        $email = new Email();
        $required = new Required();
        $number = new Number();
        $data = ["email" => "test@", "num1" => "not a number"];
        $rules = [
            "email" => $email,
            "num1" => $number,
            "num2" => [$required, $number],
        ];
        $expected = [
            "email" => ["email" => $email->message()],
            "num1" => ["number" => $number->message()],
            "num2" => [
                "required" => $required->message(),
                "number" => $number->message()
            ],
        ];
        $v = new Validator($data);
        try {
            $v->validate($rules);
            $this->fail("Did not throw Validation Exception");
        } catch (ValidationException $e) {
            $this->assertEquals($expected, $e->errors());
        }
    }
}
