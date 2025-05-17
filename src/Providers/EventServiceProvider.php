<?php

namespace LightWeight\Providers;

use DI\Container as DIContainer;
use LightWeight\Container\Container;
use LightWeight\Events\Contracts\EventDispatcherContract;
use LightWeight\Events\Contracts\EventSubscriberContract;
use LightWeight\Events\Contracts\ListenerContract;
use LightWeight\Events\EventDispatcher;
use LightWeight\Log\Contracts\LoggerContract;
use LightWeight\Log\Handlers\EventLogHandler;
use LightWeight\Providers\Contracts\ServiceProviderContract;

/**
 * Service provider for the event system
 */
class EventServiceProvider implements ServiceProviderContract
{
    /**
     * List of default event listeners to register
     * 
     * Override this property in child classes to register app-specific listeners
     *
     * @var array<string, array<ListenerContract|callable>>
     */
    protected array $listen = [];

    /**
     * Register event-related services in the container
     *
     * @param DIContainer $serviceContainer The DI container
     * @return void
     */
    public function registerServices(DIContainer $serviceContainer)
    {
        // Register the event dispatcher
        $serviceContainer->set(
            EventDispatcherContract::class,
            function () use ($serviceContainer) {
                $dispatcher = new EventDispatcher();
                
                // Register default listeners
                $this->registerEventListeners($dispatcher);
                
                // Register subscribers from config
                $this->registerConfigSubscribers($dispatcher);
                
                // Configure event logging
                $this->configureEventLogging($dispatcher, $serviceContainer);
                
                return $dispatcher;
            }
        );
    }
    
    /**
     * Register default event listeners
     *
     * @param EventDispatcherContract $dispatcher
     * @return void
     */
    protected function registerEventListeners(EventDispatcherContract $dispatcher): void
    {
        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                if (is_string($listener) && class_exists($listener)) {
                    // Si es un nombre de clase, usamos el contenedor para instanciarla con sus dependencias
                    $dispatcher->listen($event, function ($event) use ($listener) {
                        $instance = Container::make($listener);
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
    protected function registerConfigSubscribers(EventDispatcherContract $dispatcher): void
    {
        $subscribers = config('events.subscribers', []);
        
        foreach ($subscribers as $subscriber) {
            if (class_exists($subscriber)) {
                // Usar el contenedor para instanciar el suscriptor
                $instance = Container::make($subscriber);
                
                if ($instance instanceof EventSubscriberContract) {
                    // Registrar oyentes mediante el método subscribe
                    $instance->subscribe($dispatcher);
                }
            }
        }
    }

    /**
     * Configure event logging if enabled
     *  
     * @param EventDispatcherContract $dispatcher
     * @param DIContainer $container
     * @return void
     */
    protected function configureEventLogging(EventDispatcherContract $dispatcher, DIContainer $container): void
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
