<?php

namespace LightWeight\Events;

use LightWeight\Container\Container;
use LightWeight\Events\Contracts\EventDispatcherContract;
use LightWeight\Events\Contracts\EventInterface;
use LightWeight\Events\Contracts\ListenerInterface;

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
     * @param ListenerInterface|callable|string $listener The listener to register
     * @return void
     */
    public function listen(string $eventName, ListenerInterface|callable|string $listener): void
    {
        $this->listeners[$eventName][] = $listener;
    }
    
    /**
     * Dispatch an event to all registered listeners
     *
     * @param EventInterface|string $event The event object or event name
     * @param array $payload Optional payload if event name is provided instead of object
     * @return void
     */
    public function dispatch(EventInterface|string $event, array $payload = []): void
    {
        // Convert event name to an Event object if needed
        if (is_string($event)) {
            $eventObj = new GenericEvent($event, $payload);
        } else {
            $eventObj = $event;
        }
        
        $eventName = $eventObj->getName();
        
        // No listeners for this event
        if (!$this->hasListeners($eventName)) {
            return;
        }
        
        // Call each listener
        foreach ($this->listeners[$eventName] as $listener) {
            if ($listener instanceof ListenerInterface) {
                $listener->handle($eventObj);
            } elseif (is_callable($listener)) {
                call_user_func($listener, $eventObj);
            } elseif (is_string($listener) && class_exists($listener)) {
                // Instantiate listener class using dependency injection container
                $instance = Container::make($listener);
                
                if ($instance instanceof ListenerInterface) {
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
