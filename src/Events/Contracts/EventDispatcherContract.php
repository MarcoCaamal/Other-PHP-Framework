<?php

namespace LightWeight\Events\Contracts;

/**
 * Interface for the event dispatcher
 */
interface EventDispatcherContract
{
    /**
     * Register an event listener
     *
     * @param string $eventName The name of the event to listen for
     * @param ListenerInterface|callable $listener The listener to register
     * @return void
     */
    public function listen(string $eventName, ListenerInterface|callable $listener): void;
    
    /**
     * Dispatch an event
     *
     * @param EventInterface|string $event The event object or event name
     * @param array $payload Optional payload if event name is provided instead of object
     * @return void
     */
    public function dispatch(EventInterface|string $event, array $payload = []): void;
    
    /**
     * Remove all listeners for a specific event
     *
     * @param string|null $eventName The event name or null to remove all listeners
     * @return void
     */
    public function forget(?string $eventName = null): void;
    
    /**
     * Check if an event has any registered listeners
     *
     * @param string $eventName The event name
     * @return bool
     */
    public function hasListeners(string $eventName): bool;
}
