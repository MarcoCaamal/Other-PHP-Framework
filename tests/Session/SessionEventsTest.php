<?php

namespace LightWeight\Tests\Session;

use LightWeight\Events\Contracts\EventDispatcherInterface;
use LightWeight\Events\EventDispatcher;
use LightWeight\Events\SessionStartedEvent;
use LightWeight\Session\Contracts\SessionStorageContract;
use LightWeight\Session\PhpNativeSessionStorage;
use PHPUnit\Framework\TestCase;

/**
 * Session storage mock that works with the event dispatcher
 */
class DispatcherAwareSessionStorage implements SessionStorageContract
{
    private $sessionId = 'test_session_id';
    private $dispatcher;
    
    public function __construct(EventDispatcherInterface $dispatcher) 
    {
        $this->dispatcher = $dispatcher;
    }
    
    public function start()
    {
        // Simulate firing the event with the real dispatcher
        $event = new SessionStartedEvent([
            'session_id' => $this->sessionId,
            'is_new' => true,
            'session_data' => ['test' => 'data']
        ]);
        
        $this->dispatcher->dispatch($event);
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
    private $dispatcher;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->dispatcher = new EventDispatcher();
    }
    
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
    
    public function testEventDispatcherRegistersAndHandlesSessionStartedEvent()
    {
        $eventHandled = false;
        $capturedEvent = null;
        
        // Register a listener for the session.started event
        $this->dispatcher->listen('session.started', function($event) use (&$eventHandled, &$capturedEvent) {
            $eventHandled = true;
            $capturedEvent = $event;
        });
        
        // Create storage with our dispatcher
        $storage = new DispatcherAwareSessionStorage($this->dispatcher);
        
        // Start the session which will fire the event through the dispatcher
        $storage->start();
        
        // Assert the event was handled
        $this->assertTrue($eventHandled, "The session.started event listener should have been called");
        $this->assertInstanceOf(SessionStartedEvent::class, $capturedEvent);
        $this->assertEquals('test_session_id', $capturedEvent->getSessionId());
        $this->assertTrue($capturedEvent->isNew());
        $this->assertEquals(['test' => 'data'], $capturedEvent->getSessionData());
    }
    
    public function testMultipleListenersForSessionStartedEvent()
    {
        $callCount = 0;
        
        // Register multiple listeners
        $this->dispatcher->listen('session.started', function() use (&$callCount) {
            $callCount++;
        });
        
        $this->dispatcher->listen('session.started', function() use (&$callCount) {
            $callCount++;
        });
        
        $this->dispatcher->listen('session.started', function() use (&$callCount) {
            $callCount++;
        });
        
        // Create storage with our dispatcher
        $storage = new DispatcherAwareSessionStorage($this->dispatcher);
        
        // Start the session which will fire the event through the dispatcher
        $storage->start();
        
        // Assert all listeners were called
        $this->assertEquals(3, $callCount, "All three session.started event listeners should have been called");
    }
    
    public function testEventDataIsAvailableInListener()
    {
        $sessionData = null;
        
        // Register a listener that checks the data
        $this->dispatcher->listen('session.started', function($event) use (&$sessionData) {
            $sessionData = $event->getSessionData();
        });
        
        // Create custom event with specific test data
        $event = new SessionStartedEvent([
            'session_id' => 'custom_id',
            'is_new' => false,
            'session_data' => ['user_id' => 123, 'last_login' => '2025-05-16']
        ]);
        
        // Dispatch the event
        $this->dispatcher->dispatch($event);
        
        // Verify the data was received in the listener
        $this->assertNotNull($sessionData, "The listener should have received session data");
        $this->assertEquals(123, $sessionData['user_id']);
        $this->assertEquals('2025-05-16', $sessionData['last_login']);
    }
}
