<?php

namespace LightWeight\Events\Contracts;

/**
 * Interface for event listeners in the system.
 */
interface ListenerContract
{
    /**
     * Handle the event
     *
     * @param EventContract $event The event to handle
     * @return void
     */
    public function handle(EventContract $event): void;
}
