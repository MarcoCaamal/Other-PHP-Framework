<?php

namespace LightWeight\Events\System;

use LightWeight\Events\Event;
use LightWeight\Routing\Route;

/**
 * Event fired when a route has been matched
 */
class RouterMatched extends Event
{
    /**
     * Get the name of the event
     *
     * @return string
     */
    public function getName(): string
    {
        return 'router.matched';
    }
    
    /**
     * Get the matched route
     *
     * @return Route
     */
    public function getRoute(): Route
    {
        return $this->data['route'] ?? null;
    }
    
    /**
     * Get the route URI
     *
     * @return string
     */
    public function getUri(): string
    {
        return $this->data['uri'] ?? '';
    }
    
    /**
     * Get the HTTP method
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->data['method'] ?? '';
    }
}
