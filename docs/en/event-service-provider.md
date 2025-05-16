# Event Service Provider

> 🌐 [Documentación en Español](../es/event-service-provider.md)

The LightWeight framework now includes a dedicated service provider for the event system. This provider simplifies the configuration and registration of global listeners for your application.

## EventServiceProvider

The `EventServiceProvider` is responsible for:
- Registering the implementation of the `EventDispatcherInterface` in the container
- Facilitating the registration of default listeners
- Automatically loading subscribers from the configuration

## Configuration

The `config/events.php` configuration file allows you to configure aspects of the event system:

```php
return [
    /**
     * Event subscribers
     * 
     * List of subscriber classes that will be automatically registered with the event dispatcher.
     * Each subscriber class must have a subscribe method that accepts an EventDispatcherInterface
     * instance as its only parameter.
     */
    'subscribers' => [
        App\Events\Subscribers\UserEventSubscriber::class,
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
        'application.bootstrapped',
    ],
];
```

## Creating your own Event Service Provider

You can extend the `EventServiceProvider` to register specific listeners for your application:

```php
<?php

namespace App\Providers;

use App\Events\Listeners\SendWelcomeEmail;
use LightWeight\Events\Contracts\EventDispatcherInterface;
use LightWeight\Providers\EventServiceProvider as BaseEventServiceProvider;

class AppEventServiceProvider extends BaseEventServiceProvider
{
    /**
     * List of listeners to register
     */
    protected array $listen = [
        'user.registered' => [
            SendWelcomeEmail::class,
        ],
        'application.bootstrapped' => [
            function ($event) {
                // Perform tasks when the application finishes initializing
            }
        ]
    ];
    
    /**
     * Register additional event-related services
     */
    public function registerServices($container)
    {
        // Call the parent method to register the EventDispatcher
        parent::registerServices($container);
        
        // Add additional event-related services if necessary
    }
}
```

Then, register your provider in `config/providers.php`:

```php
return [
    'boot' => [
        // ... other providers
        App\Providers\AppEventServiceProvider::class,
    ],
    // ...
];
```

## Event Subscribers

Event subscribers are classes that allow you to group multiple related listeners in one place. To create a subscriber:

```php
<?php

namespace App\Events\Subscribers;

use LightWeight\Events\Contracts\EventDispatcherInterface;
use LightWeight\Events\Contracts\EventInterface;
use LightWeight\Events\Contracts\EventSubscriberInterface;

class UserEventSubscriber implements EventSubscriberInterface
{
    /**
     * Get the events handled by this subscriber
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'user.registered' => 'onUserRegistered',
            'user.login' => 'onUserLogin'
        ];
    }
    
    /**
     * Register the listeners for the subscriber
     */
    public function subscribe(EventDispatcherInterface $dispatcher): void
    {
        $dispatcher->listen('user.registered', function (EventInterface $event) {
            $this->onUserRegistered($event);
        });
        
        $dispatcher->listen('user.login', function (EventInterface $event) {
            $this->onUserLogin($event);
        });
    }
    
    /**
     * Handle the user registration event
     */
    public function onUserRegistered(EventInterface $event): void
    {
        // Implementation...
    }
    
    /**
     * Handle the login event
     */
    public function onUserLogin(EventInterface $event): void
    {
        // Implementation...
    }
}
```

Subscribers provide an organized way to manage related listeners.
