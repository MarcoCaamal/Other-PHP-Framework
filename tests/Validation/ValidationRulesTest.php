<?php

namespace Junk\Tests\Validation;

use Junk\Validation\Rules\Email;
use Junk\Validation\Rules\Required;
use Junk\Validation\Rules\RequiredWith;
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
            ["", true],
            [null, true],
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
    public function test_required_with()
    {
        $rule = new RequiredWith('other');
        $data = ['other' => 10, 'test' => 5];
        $this->assertTrue($rule->isValid('test', $data));
        $data = ['other' => 10];
        $this->assertFalse($rule->isValid('test', $data));
    }
}
