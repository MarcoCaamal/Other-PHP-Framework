<?php

namespace SMFramework\Tests\Config;

use PHPUnit\Framework\TestCase;
use SMFramework\Config\Config;

class ConfigTest extends TestCase
{
    public function testConfig()
    {
        Config::load(__DIR__ . '/test-config');
        $this->assertEquals([2, 3, 4], Config::get("values"));
        $this->assertEquals("value", Config::get("test.some"));
        $this->assertEquals("value", Config::get("test.another.nested.key"));
        $this->assertEquals(["nested" => ["key" => "value"]], Config::get("test.another"));
        $this->assertNull(Config::get("null"));
        $this->assertNull(Config::get("test.unknown"));
        $this->assertNull(Config::get("test.another.unknown"));
        $this->assertNull(Config::get("test.very.unknown"));
    }
}
