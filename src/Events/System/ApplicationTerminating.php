<?php

namespace LightWeight\Events\System;

use LightWeight\Events\Event;

/**
 * Event fired when the application is about to terminate
 */
class ApplicationTerminating extends Event
{
    /**
     * Get the name of the event
     *
     * @return string
     */
    public function getName(): string
    {
        return 'application.terminating';
    }
}
