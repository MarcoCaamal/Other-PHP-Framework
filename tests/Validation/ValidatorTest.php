<?php

namespace OtherPHPFramework\Tests\Validation;

use OtherPHPFramework\Validation\Exceptions\ValidationException;
use OtherPHPFramework\Validation\Rules\Email;
use OtherPHPFramework\Validation\Rules\LessThan;
use OtherPHPFramework\Validation\Rules\Number;
use OtherPHPFramework\Validation\Rules\Required;
use OtherPHPFramework\Validation\Rules\RequiredWith;
use OtherPHPFramework\Validation\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
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

    public function test_throws_validation_exception_on_invalid_data()
    {
        $this->expectException(ValidationException::class);
        $v = new Validator(["test" => "test"]);
        $v->validate(["test" => new Number()]);
    }

    /**
     * @depends test_basic_validation_passes
     */
    public function test_multiple_rules_validation()
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
}
