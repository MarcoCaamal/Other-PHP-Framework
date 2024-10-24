<?php

namespace OtherPHPFramework\Tests\Session;

use OtherPHPFramework\Session\Contracts\SessionContract;
use OtherPHPFramework\Session\Session;
use PHPUnit\Framework\TestCase;

class SessionTest extends TestCase
{
    private function createMockSessionStorage()
    {
        // $mock = $this->getMockBuilder(SessionContract::class)->getMock();
        $mock = $this->createMock(SessionContract::class);
        $mock->method("id")->willReturn("id");
        $mock->storage = [];
        $mock->method("has")->willReturnCallback(fn ($key) => isset($mock->storage[$key]));
        $mock->method("get")->willReturnCallback(fn ($key) => $mock->storage[$key] ?? null);
        $mock->method("set")->willReturnCallback(fn ($key, $value) => $mock->storage[$key] = $value);
        $mock->method("remove")->willReturnCallback(function ($key) use ($mock) {
            unset($mock->storage[$key]);
        });
        return $mock;
    }
    public function testAgeFlashData()
    {
        $mock = $this->createMockSessionStorage();
        if(!$mock instanceof SessionContract) {
            $this->fail('The object mock isn\'t instace of SessionContract Class');
        }
        $s1 = new Session($mock);
        $s1->set("test", "hello");
        $this->assertTrue(isset($mock->storage["test"]));
        // Check flash data
        $this->assertEquals(["old" => [], "new" => []], $mock->storage[$s1::FLASH_KEY]);
        $s1->flash("alert", "some alert");
        $this->assertEquals(["old" => [], "new" => ["alert"]], $mock->storage[$s1::FLASH_KEY]);
        // Check flash data is still set and keys are aged
        $s1->__destruct();
        $this->assertTrue(isset($mock->storage["alert"]));
        $this->assertEquals(["old" => ["alert"], "new" => []], $mock->storage[$s1::FLASH_KEY]);
        // Create new session and check previous session flash data
        $s2 = new Session($mock);
        $this->assertEquals(["old" => ["alert"], "new" => []], $mock->storage[$s2::FLASH_KEY]);
        $this->assertTrue(isset($mock->storage["alert"]));
        // Destroy session and check that flash keys are removed
        $s2->__destruct();
        $this->assertEquals(["old" => [], "new" => []], $mock->storage[$s2::FLASH_KEY]);
        $this->assertFalse(isset($mock->storage["alert"]));
    }
}
