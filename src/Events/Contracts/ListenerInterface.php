<?php

namespace LightWeight\Events\Contracts;

/**
 * Interface for event listeners in the system.
 */
interface ListenerInterface
{
    /**
     * Handle the event
     *
     * @param EventInterface $event The event to handle
     * @return void
     */
    public function handle(EventInterface $event): void;
}
