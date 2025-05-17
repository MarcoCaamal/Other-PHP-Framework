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
- `view.rendering`: Fired before a view is rendered
- `view.rendered`: Fired after a view has been rendered
- `session.started`: Fired when a session is started
- `auth.attempt`: Fired when a user attempts to authenticate
- `auth.login`: Fired when a user successfully logs in
- `auth.logout`: Fired when a user logs out

For more details about authentication events, see the [Authentication Events documentation](auth-events.md).

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

### `view.rendering` Event

You can use this event to modify view parameters or perform actions before a view is rendered:

```php
on('view.rendering', function ($event) {
    $view = $event->getView();
    $params = $event->getParams();
    $layout = $event->getLayout();
    
    // Add global data to all views
    if (!isset($params['user']) && auth()->check()) {
        $params['user'] = auth()->user();
        
        // You can modify parameters by accessing them through the $event->getData() array
        $event->getData()['params'] = $params;
    }
    
    // Log view rendering for debugging
    app('log')->debug("Rendering view: {$view}");
    
    // Perform custom actions for specific views
    if ($view === 'admin/dashboard') {
        // Log admin access or perform security checks
    }
});
```

### `view.rendered` Event

This event is useful for post-processing rendered content or logging view performance:

```php
on('view.rendered', function ($event) {
    $view = $event->getView();
    $content = $event->getContent();
    
    // Measure and log rendering time for specific views
    if (str_starts_with($view, 'reports/')) {
        app('log')->info("Report view {$view} rendered in " . (microtime(true) - FRAMEWORK_START_TIME) . " seconds");
    }
    
    // You could also perform content manipulation after rendering if needed
    // Note: At this point, the content has already been sent to the output buffer
    // so modifications won't affect the current response
    
    // However, you can capture metrics or analyze the rendered content
    if (config('app.debug') && strlen($content) > 1000000) {
        app('log')->warning("Large view rendered: {$view} - Size: " . strlen($content) . " bytes");
    }
});
```

### `session.started` Event

This event allows you to perform actions when a user session is started:

```php
on('session.started', function ($event) {
    $sessionId = $event->getSessionId();
    $isNew = $event->isNew();
    $sessionData = $event->getSessionData();
    
    // Track session metrics
    app('stats')->incrementCounter('active_sessions');
    
    // Log new session creation
    if ($isNew) {
        app('log')->info("New session started: {$sessionId}");
    }
    
    // Implement custom session security checks
    if (isset($sessionData['user_id'])) {
        $user = app('db')->table('users')->find($sessionData['user_id']);
        
        // Check for suspicious activity
        if ($user && $user->suspicious_activity_flag) {
            app('log')->warning("User with suspicious activity flag logged in: User ID {$user->id}");
            
            // You could also invalidate the session or add additional security checks
            // session()->invalidate();
        }
    }
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

## System Events

The framework includes several built-in events:

### Application Events

- `app.booting`: Fired when the application is starting but before bootstrapping
- `app.bootstrapped`: Fired when the application is fully bootstrapped
- `app.error`: Fired when an unhandled error occurs

### HTTP Events

- `request.received`: Fired when a new HTTP request is received
- `response.before`: Fired before sending an HTTP response
- `response.after`: Fired after sending an HTTP response

### Router Events

- `router.matched`: Fired when the router matches a route to the current request
- `route.not_found`: Fired when no route matches the current request
- `route.unauthorized`: Fired when authorization to a route is denied

### View Events

- `view.rendering`: Fired before a view is rendered
- `view.rendered`: Fired after a view is rendered

### Session Events

- `session.started`: Fired when a new session is started

### Authentication Events

- `auth.attempt`: Fired when a login attempt is made
- `auth.login`: Fired when a user is logged in successfully
- `auth.logout`: Fired when a user logs out

### Model Events

- `model.creating`: Fired before a model is created
- `model.created`: Fired after a model is created
- `model.updating`: Fired before a model is updated
- `model.updated`: Fired after a model is updated
- `model.deleting`: Fired before a model is deleted
- `model.deleted`: Fired after a model is deleted

For more information about model events, see the [Model Events](model-events.md) guide.
