# Events System Guide

The LightWeight framework includes a simple but powerful event system based on the observer pattern. This system allows your application to react to different events that occur during execution.

## Basic Concepts

- **Events**: Events are objects that represent something that has occurred in the application. For example, `ApplicationBootstrapped` is fired when the application finishes initializing.
  
- **Listeners**: Listeners are functions or classes that respond to specific events. When an event occurs, all listeners registered for that event are executed.

## Firing Events

There are two ways to fire events:

### 1. Using the `event()` helper function

```php
// Using an event name (string)
event('user.registered', ['user' => $user]);

// Using an event object
$event = new UserRegistered($user);
event($event);
```

### 2. Using the event dispatcher directly

```php
app(EventDispatcherInterface::class)->dispatch('user.registered', ['user' => $user]);

// Or using the App instance
app()->events()->dispatch('user.registered', ['user' => $user]);
```

## Registering Event Listeners

There are several ways to register event listeners:

### 1. Using the `on()` helper function

```php
// Using a closure
on('user.registered', function($event) {
    $user = $event->getData()['user'];
    // Send welcome email
    mailTemplate($user->email, 'Welcome', 'welcome', ['userName' => $user->name]);
});

// Using a class method
on('user.registered', [UserNotifier::class, 'sendWelcomeEmail']);
```

### 2. Using the event dispatcher directly

```php
app(EventDispatcherInterface::class)->addListener('user.registered', function($event) {
    // Handle the event
});
```

### 3. Using Event Subscribers

An event subscriber is a class that can register multiple listeners for different events:

```php
class UserEventSubscriber implements SubscriberInterface
{
    public function getEvents(): array
    {
        return [
            'user.registered' => 'onUserRegistered',
            'user.login' => 'onUserLogin',
            'user.logout' => 'onUserLogout'
        ];
    }
    
    public function onUserRegistered($event)
    {
        // Handle user registered event
    }
    
    public function onUserLogin($event)
    {
        // Handle user login event
    }
    
    public function onUserLogout($event)
    {
        // Handle user logout event
    }
}

// Register the subscriber
app(EventDispatcherInterface::class)->addSubscriber(new UserEventSubscriber());
```

## Event Service Provider

For a cleaner organization, you can register all your event listeners in an `EventServiceProvider`:

```php
<?php

namespace App\Providers;

use LightWeight\Events\Contracts\SubscriberInterface;
use LightWeight\Events\ServiceProvider;
use App\Events\Subscribers\UserEventSubscriber;
use App\Events\Subscribers\OrderEventSubscriber;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register event listeners and subscribers
     */
    public function register(): void
    {
        // Register individual event listeners
        $this->events->addListener('app.bootstrapped', function($event) {
            // Do something when the application has bootstrapped
        });
        
        // Register event subscribers
        $this->registerSubscribers([
            UserEventSubscriber::class,
            OrderEventSubscriber::class,
        ]);
    }
    
    /**
     * Register an array of subscribers
     */
    protected function registerSubscribers(array $subscribers): void
    {
        foreach ($subscribers as $subscriber) {
            $this->events->addSubscriber(app($subscriber));
        }
    }
}
```

Then register the service provider in your application's bootstrap process:

```php
$app->register(App\Providers\EventServiceProvider::class);
```

For more details on service providers, see the [Event Service Provider documentation](event-service-provider.md).

## System Events

LightWeight implements the following system events:

- `app.bootstrapped`: Fired when the application finishes bootstrapping
- `application.terminating`: Fired when the application is about to shut down
- `router.matched`: Fired when a route has been matched with the current request

> **Note**: Other common events like `view.rendering`, `auth.login`, `model.creating`, etc., might be implemented in future versions of the framework, but are currently not available as built-in events.

## System Event Examples

### `router.matched` Event

You can use this event to perform actions when a specific route is accessed:

```php
on('router.matched', function ($event) {
    $route = $event->getRoute();
    $uri = $event->getUri();
    $method = $event->getMethod();
    
    // Log access to a specific route
    if ($uri === '/admin/dashboard') {
        app('log')->info("Admin dashboard access detected. Method: {$method}");
    }
    
    // You can also perform additional security checks
    // or any other operations needed when certain routes are accessed
});
```

### `app.bootstrapped` Event

This event is useful for running code after the application has finished initializing:

```php
on('app.bootstrapped', function ($event) {
    // Initialize services that should be available throughout the application lifecycle
    app('cache')->warmUp();
    
    // Or configure global values
    app('settings')->load();
});
```

### `application.terminating` Event

You can use this event to perform cleanup or final actions before the application terminates:

```php
on('application.terminating', function ($event) {
    // Get the response being sent
    $response = $event->getData()['response'];
    
    // Log response time
    $startTime = app('timer')->getStartTime();
    $endTime = microtime(true);
    app('log')->info("Response time: " . ($endTime - $startTime) . " seconds");
    
    // Save statistics or perform final cleanup
    app('stats')->save();
});
```

## Creating Custom Events

You can create custom event classes:

```php
<?php

namespace App\Events;

use LightWeight\Events\Event;
use App\Models\Order;

class OrderShipped extends Event
{
    protected Order $order;
    
    public function __construct(Order $order)
    {
        $this->order = $order;
    }
    
    public function getOrder(): Order
    {
        return $this->order;
    }
}
```

Then dispatch the event:

```php
$order = Order::find($id);
event(new OrderShipped($order));
```

And register a listener:

```php
on(OrderShipped::class, function(OrderShipped $event) {
    $order = $event->getOrder();
    // Send notification, update inventory, etc.
});
```

## Best Practices

1. **Use event objects**: For complex events, create dedicated event classes instead of using string names.
2. **Use subscribers for related events**: Group related event listeners in subscriber classes.
3. **Keep listeners focused**: Each listener should have a single responsibility.
4. **Don't rely on execution order**: Don't assume that listeners will be executed in a specific order.
5. **Be careful with performance**: If you have many event listeners, be mindful of the performance impact.
6. **Use events for decoupling**: Events are great for decoupling components, but don't overuse them.
