<?php

namespace OtherPHPFramework\Tests\Validation;

use OtherPHPFramework\Validation\Rules\Email;
use OtherPHPFramework\Validation\Rules\LessThan;
use OtherPHPFramework\Validation\Rules\Number;
use OtherPHPFramework\Validation\Rules\Required;
use OtherPHPFramework\Validation\Rules\RequiredWhen;
use OtherPHPFramework\Validation\Rules\RequiredWith;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ValidationRulesTest extends TestCase
{
    public static function emailsProvider()
    {
        return [
            ["test@test.com", true],
            ["antonio@mastermind.ac", true],
            ["test@testcom", false],
            ["test@test.", false],
            ["antonio@", false],
            ["antonio@.", false],
            ["antonio", false],
            ["@", false],
            ["", false],
            [null, false],
            [4, false],
        ];
    }
    #[DataProvider('emailsProvider')]
    public function testEmail($email, $expected)
    {
        $data = ['email' => $email];
        $rule = new Email();
        $this->assertEquals($expected, $rule->isValid('email', $data));
    }
    public static function requiredDataProvider()
    {
        return [
            ["", false],
            [null, false],
            [5, true],
            ["test", true],
        ];
    }
    #[DataProvider("requiredDataProvider")]
    public function testRequired($value, $expected)
    {
        $data = ['test' => $value];
        $rule = new Required();
        $this->assertEquals($expected, $rule->isValid('test', $data));
    }
    public function testRequiredWith()
    {
        $rule = new RequiredWith('other');
        $data = ['other' => 10, 'test' => 5];
        $this->assertTrue($rule->isValid('test', $data));
        $data = ['other' => 10];
        $this->assertFalse($rule->isValid('test', $data));
    }
    public static function lessThanDataProvider()
    {
        return [
            [5, 5, false],
            [5, 6, false],
            [5, 3, true],
            [5, null, false],
            [5, "", false],
            [5, "test", false],
        ];
    }
    #[DataProvider("lessThanDataProvider")]
    public function testLessThan($value, $check, $expected)
    {
        $rule = new LessThan($value);
        $data = ["test" => $check];
        $this->assertEquals($expected, $rule->isValid("test", $data));
    }
    public static function numbersProvider()
    {
        return [
            [0, true],
            [1, true],
            [1.5, true],
            [-1, true],
            [-1.5, true],
            ["0", true],
            ["1", true],
            ["1.5", true],
            ["-1", true],
            ["-1.5", true],
            ["test", false],
            ["1test", false],
            ["-5test", false],
            ["", false],
            [null, false],
        ];
    }
    #[DataProvider("numbersProvider")]
    public function testNumber($n, $expected)
    {
        $rule = new Number();
        $data = ["test" => $n];
        $this->assertEquals($expected, $rule->isValid("test", $data));
    }
    public static function requiredWhenDataProvider()
    {
        return [
            ["other", "=", "value", ["other" => "value"], "test", false],
            ["other", "=", "value", ["other" => "value", "test" => 1], "test", true],
            ["other", "=", "value", ["other" => "not value"], "test", true],
            ["other", ">", 5, ["other" => 1], "test", true],
            ["other", ">", 5, ["other" => 6], "test", false],
            ["other", ">", 5, ["other" => 6, "test" => 1], "test", true],
        ];
    }
    #[DataProvider("requiredWhenDataProvider")]
    public function testRequiredWhen($other, $operator, $compareWith, $data, $field, $expected)
    {
        $rule = new RequiredWhen($other, $operator, $compareWith);
        $this->assertEquals($expected, $rule->isValid($field, $data));
    }
}
