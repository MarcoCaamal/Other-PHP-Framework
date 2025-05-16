<?php

namespace LightWeight\Events;

use LightWeight\Events\Contracts\EventInterface;

/**
 * Generic event class for simple string-based events
 */
class GenericEvent implements EventInterface
{
    /**
     * Event name
     *
     * @var string
     */
    protected string $name;
    
    /**
     * Event data
     *
     * @var array
     */
    protected array $data;
    
    /**
     * Constructor
     *
     * @param string $name The event name
     * @param array $data The event data
     */
    public function __construct(string $name, array $data = [])
    {
        $this->name = $name;
        $this->data = $data;
    }
    
    /**
     * Get the name of the event
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
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
