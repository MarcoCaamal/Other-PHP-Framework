<?php

namespace LightWeight\App;

use Closure;
use LightWeight\App;
use LightWeight\Events\Contracts\EventDispatcherInterface;
use LightWeight\Events\Contracts\EventInterface;
use LightWeight\Events\Contracts\ListenerInterface;
use LightWeight\Events\EventDispatcher;

/**
 * Prepare and set up the event system
 */
function registerEventSystem(): void
{
    // Register the event dispatcher in the container
    singleton(EventDispatcherInterface::class, EventDispatcher::class);
    
    // Make it accessible from the App instance
    $app = app(App::class);
    $app->events = app(EventDispatcherInterface::class);
}

/**
 * Register an event listener
 *
 * @param string $event The event name or class
 * @param ListenerInterface|callable $listener The listener to register
 * @return void
 */
function listen(string $event, ListenerInterface|callable $listener): void
{
    $dispatcher = app(EventDispatcherInterface::class);
    $dispatcher->listen($event, $listener);
}

/**
 * Dispatch an event
 *
 * @param EventInterface|string $event The event to dispatch
 * @param array $payload Optional payload if using a string event name
 * @return void
 */
function dispatch(EventInterface|string $event, array $payload = []): void
{
    $dispatcher = app(EventDispatcherInterface::class);
    $dispatcher->dispatch($event, $payload);
}
