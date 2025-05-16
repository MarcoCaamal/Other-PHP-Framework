<?php

namespace LightWeight\Events\System;

use LightWeight\Events\Event;

/**
 * Event fired when the application has been bootstrapped
 */
class ApplicationBootstrapped extends Event
{
    /**
     * Get the name of the event
     *
     * @return string
     */
    public function getName(): string
    {
        return 'application.bootstrapped';
    }
}
