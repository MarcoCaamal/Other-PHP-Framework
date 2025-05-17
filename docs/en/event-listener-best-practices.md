# Best Practices for Event Listeners

> 🌐 [Documentación en Español](../es/event-listener-best-practices.md)

This document outlines best practices for working with event listeners in the LightWeight framework.

## Types of Event Listeners

LightWeight supports two types of event listeners:

1. **Class-based listeners**: Defined as classes that are instantiated by the container
2. **Closure-based listeners**: Anonymous functions defined inline

## Registering Class-based Listeners

Class-based listeners should be registered in the `$listen` property of your `AppEventServiceProvider`:

```php
protected array $listen = [
    'user.registered' => [
        \App\Events\Listeners\SendWelcomeEmailListener::class,
        \App\Events\Listeners\CreateUserProfileListener::class,
    ],
    'order.placed' => [
        \App\Events\Listeners\ProcessOrderListener::class,
    ],
];
```

## Registering Closure-based Listeners

Due to PHP restrictions, closure-based listeners cannot be defined directly in the `$listen` property. Instead, they should be registered in the `registerServices` method:

```php
public function registerServices($container)
{
    // First register class-based listeners
    parent::registerServices($container);
    
    // Then register closure-based listeners
    $dispatcher = $container->get(EventDispatcherContract::class);
    
    $dispatcher->listen('user.login', function ($event) {
        // Handle user login
    });
    
    $dispatcher->listen('application.bootstrapped', function ($event) {
        // Handle application bootstrapped
    });
}
```

## When to Use Each Type

### Use Class-based Listeners When:

- The listener logic is complex
- The listener requires dependency injection
- The same logic needs to be reused for multiple events
- You want to keep the provider class clean and focused
- You want better testability

### Use Closure-based Listeners When:

- The listener logic is simple and short
- The logic is specific to a single event and won't be reused
- You want to keep related code together for better readability
- The listener doesn't have many dependencies

## Common Pitfalls

### Problem: Using Closures in `$listen` Property

```php
// This will cause a PHP Fatal error
protected array $listen = [
    'event.name' => [
        function ($event) { /* ... */ },  // Error: Not a constant expression
    ],
];
```

The error occurs because PHP requires property initial values to be constant expressions, and closures are not constants.

### Solution: Use the `registerServices` Method

```php
// In AppEventServiceProvider
public function registerServices($container)
{
    parent::registerServices($container);
    
    $container->get(EventDispatcherContract::class)
        ->listen('event.name', function ($event) {
            // Your logic here
        });
}
```

## Performance Considerations

- Class-based listeners are resolved from the container, which allows dependency injection but has a small performance cost
- Closure-based listeners are slightly faster but cannot leverage the container's dependency injection

For most applications, this performance difference is negligible. Choose the approach that best fits your design and maintainability needs.
