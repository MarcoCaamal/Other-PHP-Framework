<?php

namespace LightWeight\Providers;

use DI\Container as DIContainer;
use LightWeight\Events\Contracts\EventDispatcherInterface;
use LightWeight\Events\Contracts\EventSubscriberInterface;
use LightWeight\Events\Contracts\ListenerInterface;
use LightWeight\Events\EventDispatcher;
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
     * @var array<string, array<ListenerInterface|callable>>
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
            EventDispatcherInterface::class,
            function () {
                $dispatcher = new EventDispatcher();
                
                // Register default listeners
                $this->registerEventListeners($dispatcher);
                
                // Register subscribers from config
                $this->registerConfigSubscribers($dispatcher);
                
                return $dispatcher;
            }
        );
    }
    
    /**
     * Register default event listeners
     *
     * @param EventDispatcherInterface $dispatcher
     * @return void
     */
    protected function registerEventListeners(EventDispatcherInterface $dispatcher): void
    {
        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                $dispatcher->listen($event, $listener);
            }
        }
    }
    
    /**
     * Register the default subscribers specified in the configuration file
     * 
     * @param EventDispatcherInterface $dispatcher
     * @return void
     */
    protected function registerConfigSubscribers(EventDispatcherInterface $dispatcher): void
    {
        $subscribers = config('events.subscribers', []);
        
        foreach ($subscribers as $subscriber) {
            if (class_exists($subscriber)) {
                $instance = new $subscriber();
                
                if ($instance instanceof EventSubscriberInterface) {
                    // Registrar oyentes mediante el método subscribe
                    $instance->subscribe($dispatcher);
                    
                    // Alternativamente, se podría implementar para usar getSubscribedEvents:
                    // foreach ($subscriber::getSubscribedEvents() as $event => $method) {
                    //     $dispatcher->listen($event, function (EventInterface $event) use ($instance, $method) {
                    //         $instance->{$method}($event);
                    //     });
                    // }
                }
            }
        }
    }
}
