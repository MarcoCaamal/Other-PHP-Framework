<?php

use LightWeight\Events\Contracts\EventDispatcherContract;
use LightWeight\Events\Contracts\EventContract;
use LightWeight\Events\Contracts\ListenerContract;

/**
 * Register an event listener
 *
 * @param string $eventName The event name or class
 * @param ListenerContract|callable $listener The listener to register
 * @return void
 */
function on(string $eventName, ListenerContract|callable $listener): void
{
    app(EventDispatcherContract::class)->listen($eventName, $listener);
}

/**
 * Dispatch an event
 *
 * @param EventContract|string $event The event object or event name
 * @param array $payload Optional payload if event name is provided instead of object
 * @return void
 */
function event(EventContract|string $event, array $payload = []): void
{
    app(EventDispatcherContract::class)->dispatch($event, $payload);
}

/**
 * Remove all listeners for a specific event
 *
 * @param string|null $eventName The event name or null to remove all listeners
 * @return void
 */
function forgetListeners(?string $eventName = null): void
{
    app(EventDispatcherContract::class)->forget($eventName);
}
