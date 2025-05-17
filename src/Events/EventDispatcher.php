<?php

namespace LightWeight\Events;

use LightWeight\Container\Container;
use LightWeight\Events\Contracts\EventDispatcherContract;
use LightWeight\Events\Contracts\EventContract;
use LightWeight\Events\Contracts\ListenerContract;

/**
 * Event dispatcher implementation
 */
class EventDispatcher implements EventDispatcherContract
{
    /**
     * Array of registered listeners
     *
     * @var array
     */
    protected array $listeners = [];
    
    /**
     * Register an event listener
     *
     * @param string $eventName The name of the event to listen for
     * @param ListenerContract|callable|string $listener The listener to register
     * @return void
     */
    public function listen(string $eventName, ListenerContract|callable|string $listener): void
    {
        $this->listeners[$eventName][] = $listener;
    }
    
    /**
     * Dispatch an event to all registered listeners
     *
     * @param EventContract|string $event The event object or event name
     * @param array $payload Optional payload if event name is provided instead of object
     * @return void
     */
    public function dispatch(EventContract|string $event, array $payload = []): void
    {
        // Convert event name to an Event object if needed
        if (is_string($event)) {
            $eventObj = new GenericEvent($event, $payload);
        } else {
            $eventObj = $event;
        }
        
        $eventName = $eventObj->getName();
        
        // Call specific event listeners
        if ($this->hasListeners($eventName)) {
            $this->callListeners($eventName, $eventObj);
        }
        
        // Call wildcard listeners
        if ($this->hasListeners('*')) {
            $this->callListeners('*', $eventObj, $eventName);
        }
    }
    
    /**
     * Call listeners for a specific event
     *
     * @param string $eventName The event name
     * @param EventContract $eventObj The event object
     * @param string|null $originalEventName Original event name for wildcard listeners
     * @return void
     */
    protected function callListeners(string $eventName, EventContract $eventObj, ?string $originalEventName = null): void
    {
        foreach ($this->listeners[$eventName] as $listener) {
            if ($listener instanceof ListenerContract) {
                $listener->handle($eventObj);
            } elseif (is_callable($listener)) {
                if ($eventName === '*' && $originalEventName !== null) {
                    // For wildcard listeners, pass the event object and original event name
                    call_user_func($listener, $eventObj, $originalEventName);
                } else {
                    call_user_func($listener, $eventObj);
                }
            } elseif (is_string($listener) && class_exists($listener)) {
                // Instantiate listener class using dependency injection container
                $instance = Container::make($listener);
                
                if ($instance instanceof ListenerContract) {
                    $instance->handle($eventObj);
                }
            }
        }
    }
    
    /**
     * Remove all listeners for a specific event
     *
     * @param string|null $eventName The event name or null to remove all listeners
     * @return void
     */
    public function forget(?string $eventName = null): void
    {
        if ($eventName === null) {
            $this->listeners = [];
        } else {
            unset($this->listeners[$eventName]);
        }
    }
    
    /**
     * Check if an event has any registered listeners
     *
     * @param string $eventName The event name
     * @return bool
     */
    public function hasListeners(string $eventName): bool
    {
        return isset($this->listeners[$eventName]) && !empty($this->listeners[$eventName]);
    }
}
