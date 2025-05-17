<?php

namespace LightWeight\Events;

use LightWeight\Events\Contracts\EventContract;

/**
 * Base class for all events in the system
 */
abstract class Event implements EventContract
{
    /**
     * Event data
     *
     * @var array
     */
    protected array $data = [];
    
    /**
     * Event constructor.
     *
     * @param array $data Optional event data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }
    
    /**
     * Get the name of the event
     * By default, uses the class name, but can be overridden
     *
     * @return string
     */
    public function getName(): string
    {
        return static::class;
    }
    
    /**
     * Get event data
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}
