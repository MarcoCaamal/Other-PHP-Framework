# Event Service Provider

> 🌐 [Documentación en Español](../es/event-service-provider.md)

The LightWeight framework now includes a dedicated service provider for the event system. This provider simplifies the configuration and registration of global listeners for your application.

## EventServiceProvider

The `EventServiceProvider` is responsible for:
- Registering the implementation of the `EventDispatcherContract` in the container
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
     * Each subscriber class must have a subscribe method that accepts an EventDispatcherContract
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
use LightWeight\Events\Contracts\EventDispatcherContract;
use LightWeight\Providers\EventServiceProvider as BaseEventServiceProvider;

class AppEventServiceProvider extends BaseEventServiceProvider
{
    /**
     * List of listeners to register
     * 
     * Note: Only class references are allowed here.
     * For closures, use the registerServices method.
     */
    protected array $listen = [
        'user.registered' => [
            SendWelcomeEmail::class,
        ],
    ];
    
    /**
     * Register application services and event listeners
     */
    public function registerServices($container)
    {
        // Call the parent method to register the EventDispatcher and class-based listeners
        parent::registerServices($container);
        
        // Get the event dispatcher to register closure-based listeners
        $dispatcher = $container->get(EventDispatcherContract::class);
        
        // Register closure-based listeners
        $dispatcher->listen('user.login', function ($event) {
            // Logic to handle user login
            $user = $event->getData()['user'] ?? null;
            if ($user) {
                // Example: Update last login date
                // $user->updateLastLogin();
            }
        });
        
        $dispatcher->listen('application.bootstrapped', function ($event) {
            // Logic to execute when the application has been bootstrapped
        });
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

use LightWeight\Events\Contracts\EventDispatcherContract;
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
    public function subscribe(EventDispatcherContract $dispatcher): void
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

## Important Note About Closures

In PHP, when defining class properties with initial values, those values must be constant expressions. Anonymous functions (closures) are not considered constant expressions, so they cannot be used directly in the `$listen` property definition.

```php
// This will cause a PHP Fatal error: Constant expression contains invalid operations
protected array $listen = [
    'event.name' => [
        function ($event) { /* ... */ },  // Not allowed as property value
    ],
];
```

Instead, register closures using the `registerServices` method as shown in the example above. This approach avoids the PHP limitation while maintaining the ability to use closures as event listeners.

For more detailed guidance, see [Event Listener Best Practices](event-listener-best-practices.md).
