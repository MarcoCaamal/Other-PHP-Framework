<?php

namespace LightWeight\Events\Contracts;

/**
 * Interface for all events in the system.
 */
interface EventContract
{
    /**
     * Get the name of the event.
     *
     * @return string
     */
    public function getName(): string;
    
    /**
     * Get event data. This can be any data that needs to be passed to listeners.
     *
     * @return array
     */
    public function getData(): array;
}
