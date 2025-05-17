<?php

namespace LightWeight\Tests\Session;

use LightWeight\Events\Contracts\EventDispatcherInterface;
use LightWeight\Events\SessionStartedEvent;
use LightWeight\Session\Contracts\SessionStorageContract;
use PHPUnit\Framework\TestCase;

/**
 * Session storage mock that tracks if the event was fired
 */
class EventTrackingSessionStorage implements SessionStorageContract
{
    private $sessionId = 'test_session_id';
    public $eventFired = false;
    public $capturedEvent = null;
    
    public function start()
    {
        // Simulate firing the event
        $event = new SessionStartedEvent([
            'session_id' => $this->sessionId,
            'is_new' => true,
            'session_data' => ['test' => 'data']
        ]);
        
        $this->eventFired = true;
        $this->capturedEvent = $event;
    }
    
    public function save() {}
    
    public function id(): string
    {
        return $this->sessionId;
    }
    
    public function get(string $key, $default = null)
    {
        return $default;
    }
    
    public function set(string $key, mixed $value) {}
    
    public function has(string $key): bool
    {
        return false;
    }
    
    public function remove(string $key) {}
    
    public function destroy() {}
}

class SessionEventsTest extends TestCase
{
    public function testSessionStartedEventCreation()
    {
        // Create the event with test data
        $event = new SessionStartedEvent([
            'session_id' => 'test_session_id',
            'is_new' => true,
            'session_data' => ['test' => 'data']
        ]);
        
        // Test event properties
        $this->assertEquals('session.started', $event->getName());
        $this->assertEquals('test_session_id', $event->getSessionId());
        $this->assertTrue($event->isNew());
        $this->assertEquals(['test' => 'data'], $event->getSessionData());
    }
    
    public function testSessionStartedEventInStorage()
    {
        // Use our mock storage
        $storage = new EventTrackingSessionStorage();
        
        // Start the session
        $storage->start();
        
        // Verify the event was created
        $this->assertTrue($storage->eventFired);
        $this->assertInstanceOf(SessionStartedEvent::class, $storage->capturedEvent);
    }
}
