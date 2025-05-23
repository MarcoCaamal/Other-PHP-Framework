<?php

namespace LightWeight\Log\Handlers;

use LightWeight\Events\Contracts\EventContract;
use LightWeight\Log\Contracts\LoggerContract;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;

/**
 * Event Log Handler
 *
 * A specialized log handler for events in the system
 */
class EventLogHandler
{
    /**
     * List of events that should not be logged
     *
     * @var array
     */
    protected array $excludedEvents = [];

    /**
     * Create a new event log handler
     *
     * @param array $excludedEvents List of events that should not be logged
     */
    public function __construct(array $excludedEvents = [])
    {
        $this->excludedEvents = $excludedEvents;
    }

    /**
     * Handle an event by logging it
     *
     * @param string $eventName
     * @param mixed $event
     * @param LoggerContract $logger
     * @return void
     */
    public function handleEvent(string $eventName, $event, LoggerContract $logger): void
    {
        // Check if this event should be excluded from logging
        if (in_array($eventName, $this->excludedEvents)) {
            return;
        }

        try {
            // Check if the event is a valid EventContract
            if (!$event instanceof EventContract) {
                $logger->debug("Non-contract event received: {$eventName}", [
                    'event' => $eventName,
                    'type' => is_object($event) ? get_class($event) : gettype($event)
                ]);

                // If it's null or not an object, just log the event name
                if ($event === null || !is_object($event)) {
                    $logger->info("Event dispatched: {$eventName}");
                    return;
                }

                // Try to extract data if the event is an object with getData method
                if (is_object($event) && method_exists($event, 'getData')) {
                    try {
                        $context = [
                            'event' => $eventName,
                            'data' => $event->getData(),
                        ];
                        $logger->info("Event dispatched: {$eventName}", $context);
                    } catch (\Exception $e) {
                        $logger->info("Event dispatched: {$eventName}", ['event' => $eventName]);
                    }
                    return;
                }

                // Just log the event name if no data can be extracted
                $logger->info("Event dispatched: {$eventName}", ['event' => $eventName]);
                return;
            }

            // Get relevant event data for logging
            $context = [
                'event' => $eventName,
                'data' => $event->getData(),
            ];

            // Log the event
            $logger->info("Event dispatched: {$eventName}", $context);
        } catch (\Throwable $e) {
            // If there's an error during event logging, log that error
            $logger->error("Error logging event {$eventName}: " . $e->getMessage(), [
                'event' => $eventName,
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
    }
}
