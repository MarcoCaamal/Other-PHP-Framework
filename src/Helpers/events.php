<?php

use LightWeight\Events\Contracts\EventDispatcherInterface;
use LightWeight\Events\Contracts\EventInterface;
use LightWeight\Events\Contracts\ListenerInterface;

/**
 * Register an event listener
 *
 * @param string $eventName The event name or class
 * @param ListenerInterface|callable $listener The listener to register
 * @return void
 */
function on(string $eventName, ListenerInterface|callable $listener): void
{
    app(EventDispatcherInterface::class)->listen($eventName, $listener);
}

/**
 * Dispatch an event
 *
 * @param EventInterface|string $event The event object or event name
 * @param array $payload Optional payload if event name is provided instead of object
 * @return void
 */
function event(EventInterface|string $event, array $payload = []): void
{
    app(EventDispatcherInterface::class)->dispatch($event, $payload);
}

/**
 * Remove all listeners for a specific event
 *
 * @param string|null $eventName The event name or null to remove all listeners
 * @return void
 */
function forget_listeners(?string $eventName = null): void
{
    app(EventDispatcherInterface::class)->forget($eventName);
}
