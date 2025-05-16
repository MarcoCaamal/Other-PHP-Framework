# Constant Expression Error in AppEventServiceProvider

> 🌐 [Documentación en Español](../es/constant-expression-error.md)

## Problem Description

In earlier versions of the LightWeight framework, you might encounter a PHP fatal error when trying to use anonymous functions (closures) within the `$listen` property definition in `AppEventServiceProvider.php`:

```php
// Code that generated the error
protected array $listen = [
    'user.registered' => [
        \App\Events\Listeners\SendWelcomeEmailListener::class,
    ],
    'user.login' => [
        function ($event) {
            // Logic to handle user login
            $user = $event->getData()['user'] ?? null;
            if ($user) {
                // Update last login date
                $user->updateLastLogin();
            }
        },
    ],
];
```

This code produces the following error:

```
PHP Fatal error: Constant expression contains invalid operations
```

## Cause of the Error

The error occurs because in PHP, initial values for class properties must be constant expressions. Constant expressions can only contain simple data types (like strings, numbers, arrays), defined constants, and simple expressions that operate with these types.

Anonymous functions (closures) are objects in PHP, not constant values, so they cannot be used as initial values for properties.

## Solution

The solution is to register the closures in the `registerServices` method instead of trying to define them in the `$listen` property:

```php
protected array $listen = [
    'user.registered' => [
        \App\Events\Listeners\SendWelcomeEmailListener::class,
    ],
    // REMOVE closures from here
];

public function registerServices($container)
{
    // Call the parent method first to register class-based listeners
    parent::registerServices($container);
    
    // Get the event dispatcher
    $dispatcher = $container->get(EventDispatcherInterface::class);
    
    // Register closure-based listeners HERE
    $dispatcher->listen('user.login', function ($event) {
        // Logic to handle user login
        $user = $event->getData()['user'] ?? null;
        if ($user) {
            // Update last login date
            $user->updateLastLogin();
        }
    });
    
    $dispatcher->listen('application.bootstrapped', function ($event) {
        // Logic to execute when the application has bootstrapped
    });
}
```

## Examples

### Incorrect Example (Will Generate Error)

```php
protected array $listen = [
    'event.name' => [
        function ($event) { /* code */ },  // ERROR: Not a constant expression
    ],
];
```

### Correct Example

```php
// In the class definition
protected array $listen = [
    'user.registered' => [
        \App\Events\Listeners\SendWelcomeEmailListener::class,  // Correct: Only class references
    ],
];

// In the registerServices method
public function registerServices($container)
{
    parent::registerServices($container);
    
    $dispatcher = $container->get(EventDispatcherInterface::class);
    $dispatcher->listen('event.name', function ($event) {
        // Your logic here
    });
}
```

## Related Documentation

For more information about best practices for working with event listeners, see:

- [Event Service Provider](event-service-provider.md)
- [Event Listener Best Practices](event-listener-best-practices.md)
- [Events System Guide](events-guide.md)
