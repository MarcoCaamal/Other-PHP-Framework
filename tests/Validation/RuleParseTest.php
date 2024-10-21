<?php

namespace OtherPHPFramework\Tests\Validation;

use OtherPHPFramework\Validation\Rule;
use OtherPHPFramework\Validation\Rules\Email;
use OtherPHPFramework\Validation\Rules\Number;
use OtherPHPFramework\Validation\Rules\Required;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RuleParseTest extends TestCase {
    protected function setUp(): void {
        Rule::loadDefaultRules();
    }

    public static function basicRules(): array {
        return [
            [Email::class, 'email'],
            [Required::class, 'required'],
            [Number::class, 'number']
        ];
    }
    #[DataProvider("basicRules")]
    public function testParseBasicRules($class, $name) {
        $this->assertInstanceOf($class, Rule::from($name));
    }
}