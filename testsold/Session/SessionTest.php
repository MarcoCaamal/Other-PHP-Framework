<?php

namespace LightWeight\Tests\Session;

use LightWeight\Session\Contracts\SessionStorageContract;
use LightWeight\Session\Session;
use PHPUnit\Framework\TestCase;

/**
 * Mock implementation of SessionStorageContract for testing
 */
class MockSessionStorage implements SessionStorageContract
{
    public array $storage = [];
    
    public function start() {}
    
    public function save() {}
    
    public function id(): string
    {
        return "id";
    }
    
    public function get(string $key, $default = null)
    {
        return $this->storage[$key] ?? $default;
    }
    
    public function set(string $key, mixed $value)
    {
        $this->storage[$key] = $value;
    }
    
    public function has(string $key): bool
    {
        return isset($this->storage[$key]);
    }
    
    public function remove(string $key)
    {
        unset($this->storage[$key]);
    }
    
    public function destroy() {}
}

class SessionTest extends TestCase
{
    private function createMockSessionStorage()
    {
        return new MockSessionStorage();
    }
    public function testAgeFlashData()
    {
        $mock = $this->createMockSessionStorage();
        if(!$mock instanceof SessionStorageContract) {
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
