<?php

namespace LightWeight\Providers;

use LightWeight\Container\Container;
use LightWeight\Events\Contracts\EventDispatcherContract;
use LightWeight\Events\Contracts\EventSubscriberContract;
use LightWeight\Events\Contracts\ListenerContract;
use LightWeight\Events\EventDispatcher;
use LightWeight\Log\Contracts\LoggerContract;
use LightWeight\Log\Handlers\EventLogHandler;

/**
 * Service provider for the event system
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * List of default event listeners to register
     * 
     * Override this property in child classes to register app-specific listeners
     *
     * @var array<string, array<ListenerContract|callable>>
     */
    protected array $listen = [];    /**
     * Proporciona definiciones para el contenedor antes de su compilación
     * 
     * @return array
     */
    public function getDefinitions(): array
    {
        return [
            EventDispatcherContract::class => \DI\create(EventDispatcher::class)
        ];
    }

    /**
     * Register event-related services in the container
     *
     * @param Container $serviceContainer The DI container
     * @return void
     */
    public function registerServices(Container $serviceContainer)
    {
        $dispatcher = $serviceContainer->get(EventDispatcherContract::class);
        $this->registerEventListeners($dispatcher, $serviceContainer);
        $this->registerConfigSubscribers($dispatcher, $serviceContainer);
        $this->configureEventLogging($dispatcher, $serviceContainer);
    }

    /**
     * Register default event listeners
     *
     * @param EventDispatcherContract $dispatcher
     * @return void
     */
    protected function registerEventListeners(EventDispatcherContract $dispatcher, Container $container): void
    {
        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                if (is_string($listener) && class_exists($listener)) {
                    // Si es un nombre de clase, usamos el contenedor para instanciarla con sus dependencias
                    $dispatcher->listen($event, function ($event) use ($listener, $container) {
                        $instance = $container->make($listener);
                        return $instance->handle($event);
                    });
                } else {
                    // Si es una función u otro tipo, lo registramos directamente
                    $dispatcher->listen($event, $listener);
                }
            }
        }
    }
    
    /**
     * Register the default subscribers specified in the configuration file
     * 
     * @param EventDispatcherContract $dispatcher
     * @return void
     */
    protected function registerConfigSubscribers(EventDispatcherContract $dispatcher, Container $container): void
    {
        // Get the list of subscribers from the configuration
        $subscribers = config('events.subscribers', []);
        
        foreach ($subscribers as $subscriber) {
            if (class_exists($subscriber)) {
                // Use the container to instantiate the subscriber
                $instance = $container->make($subscriber);
                
                if ($instance instanceof EventSubscriberContract) {
                    // Register listeners using the subscribe method
                    $instance->subscribe($dispatcher);
                }
            }
        }
    }

    /**
     * Configure event logging if enabled
     *  
     * @param EventDispatcherContract $dispatcher
     * @param Container $container
     * @return void
     */
    protected function configureEventLogging(EventDispatcherContract $dispatcher, Container $container): void
    {
        $enableEventLogging = filter_var(
            config('logging.event_logging.enabled', config('events.log_events', false)),
            FILTER_VALIDATE_BOOLEAN
        );
        // Check if event logging is enabled
        if (!$enableEventLogging) {
            return;
        }
        
        // Get excluded events from config
        $excludedEvents = config('logging.event_logging.excluded_events', config('events.log_exclude', []));
        
        // Create the event log handler
        $eventLogHandler = new EventLogHandler($excludedEvents);
        
        // Get the logger
        $logger = $container->get(LoggerContract::class);
        
        // Register a listener for all events
        $dispatcher->listen('*', function ($event, ?string $eventName = null) use ($eventLogHandler, $logger) {
            // If event name is not provided as second argument, it might be in the first parameter
            if ($eventName === null && is_string($event)) {
                $eventName = $event;
                $event = null;
            }
            
            if (!is_string($eventName)) {
                $logger->warning('Invalid event format received in event logger');
                return;
            }
            
            $eventLogHandler->handleEvent($eventName, $event, $logger);
        });
    }
}
