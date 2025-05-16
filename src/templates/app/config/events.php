<?php

/**
 * Events configuration
 * 
 * This file contains configuration for the event system.
 */
return [
    /**
     * Event subscribers
     * 
     * List of subscriber classes that will be automatically registered with the event dispatcher.
     * Each subscriber class must implement the EventSubscriberInterface.
     */
    'subscribers' => [
        // Example: App\Events\Subscribers\UserEventSubscriber::class,
        // LightWeight\Events\Subscribers\ExampleSubscriber::class,
    ],
    
    /**
     * Event logging
     * 
     * When enabled, all events will be logged for debugging purposes.
     */
    'log_events' => env('LOG_EVENTS', false),
    
    /**
     * Events that should not be logged even when event logging is enabled
     */
    'log_exclude' => [
        // Example: 'application.bootstrapped',
    ],
];
