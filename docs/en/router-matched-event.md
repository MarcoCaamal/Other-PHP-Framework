# Router.Matched Event

> 🌐 [Documentación en Español](../es/router-matched-event.md)

The `router.matched` event is fired when the LightWeight router finds a route that matches the current request. This event provides useful information about the matching route, allowing you to execute specific code when certain routes are accessed.

## When it's fired

The `router.matched` event is fired during the route resolution process, just after a match has been found, but before the route's controller or action is executed.

## Event Data

The `RouterMatched` event provides the following methods to access route information:

- `getRoute()`: Returns the `Route` instance that matched the request
- `getUri()`: Returns the requested URI
- `getMethod()`: Returns the HTTP method used in the request (GET, POST, etc.)

## Common Use Cases

### Logging access to specific routes

```php
on('router.matched', function ($event) {
    if (str_starts_with($event->getUri(), '/admin')) {
        app('log')->info(sprintf(
            "Admin section access: %s %s",
            $event->getMethod(),
            $event->getUri()
        ));
    }
});
```

### Additional security checks

```php
on('router.matched', function ($event) {
    $route = $event->getRoute();
    
    // Verification for sensitive routes
    if (str_starts_with($event->getUri(), '/api/admin')) {
        // Implement additional checks
        $token = request()->header('X-ADMIN-TOKEN');
        if (!app('security')->validateAdminToken($token)) {
            abort(403, 'Access denied');
        }
    }
});
```

### Analytics and metrics

```php
on('router.matched', function ($event) {
    // Record route usage statistics
    app('metrics')->increment('route.hits.' . str_replace('/', '.', trim($event->getUri(), '/')));
    
    // Start timer to measure performance
    app('timer')->start('route.' . $event->getUri());
});
```

## Technical Notes

The `router.matched` event is implemented in the `RouterMatched` class, which extends the base `Event` class. If you need to further customize this event or add additional functionality, you can do so by extending this class.

The event is automatically fired from the router's `resolveRoute` method, so no additional configuration is needed for it to be available.
