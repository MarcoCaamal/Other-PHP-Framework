# Middleware in LightWeight

## Introduction

Middleware provides a convenient mechanism for filtering HTTP requests entering your application. For example, LightWeight includes a middleware that verifies if your application's user is authenticated. If the user is not authenticated, the middleware will redirect them to the login screen. However, if the user is authenticated, the middleware will allow the request to proceed further into the application.

Middleware can perform a variety of tasks beyond authentication. A middleware can log all requests to your application, apply CORS headers, or compress HTTP responses. Each middleware acts as an intermediate layer that the HTTP request must pass through before reaching its final destination.

## Middleware Structure

In LightWeight, a middleware is a class that implements the `MiddlewareContract` interface. This interface requires a single `handle()` method that receives two parameters:

- The current HTTP request (`RequestContract`)
- A closure function (`Closure`) that passes the request to the next middleware or the controller

Let's look at a basic example of a middleware:

```php
<?php

namespace App\Middleware;

use LightWeight\Http\Contracts\MiddlewareContract;
use LightWeight\Http\Contracts\RequestContract;

class ExampleMiddleware implements MiddlewareContract
{
    /**
     * Process the incoming request.
     *
     * @param RequestContract $request The HTTP request
     * @param \Closure $next The next middleware or controller in the chain
     * @return mixed
     */
    public function handle(RequestContract $request, \Closure $next)
    {
        // Code to execute before the request reaches the controller
        
        // Pass the request to the next middleware or to the controller
        $response = $next($request);
        
        // Code to execute after the controller has processed the request
        
        return $response;
    }
}
```

## Creating a Middleware

To create a middleware, you can use the LightWeight CLI command:

```bash
php light make:middleware AuthMiddleware
```

This will create a new middleware file at `app/Middleware/AuthMiddleware.php`.

## Registering Middleware

For middleware to be used, it must be registered. LightWeight supports two types of middleware:

1. **Global middleware**: Applied to every HTTP request
2. **Route middleware**: Applied only to specific routes

### Registering Global Middleware

Global middleware is registered in the `bootstrap/app.php` file:

```php
// bootstrap/app.php

// ...

// Register global middleware
$app->middleware([
    \App\Middleware\TrimStrings::class,
    \App\Middleware\ConvertEmptyStringsToNull::class,
]);

// ...
```

### Registering Route Middleware

Route middleware is registered with a name in the `bootstrap/app.php` file, and then applied to specific routes:

```php
// bootstrap/app.php

// ...

// Register route middleware
$app->routeMiddleware([
    'auth' => \App\Middleware\AuthMiddleware::class,
    'cache' => \App\Middleware\CacheResponseMiddleware::class,
    'throttle' => \App\Middleware\ThrottleRequestsMiddleware::class,
]);

// ...
```

Then, you can apply the middleware to specific routes:

```php
// routes/web.php

use LightWeight\Routing\Route;

Route::get('/profile', 'ProfileController@show')->middleware('auth');

// Apply multiple middleware
Route::get('/api/users', 'Api\UserController@index')->middleware(['auth', 'throttle']);
```

## Middleware Parameters

Sometimes, your middleware may need additional parameters. For example, a throttling middleware might need to know how many requests to allow per minute. You can pass parameters to middleware by adding a colon after the middleware name, followed by the parameters:

```php
// routes/web.php

Route::get('/api/users', 'Api\UserController@index')->middleware('throttle:60,1');
```

The parameters will be passed to the middleware's `handle()` method:

```php
<?php

namespace App\Middleware;

use LightWeight\Http\Contracts\MiddlewareContract;
use LightWeight\Http\Contracts\RequestContract;

class ThrottleRequestsMiddleware implements MiddlewareContract
{
    /**
     * Process the incoming request.
     *
     * @param RequestContract $request The HTTP request
     * @param \Closure $next The next middleware or controller in the chain
     * @param int $maxAttempts Maximum number of attempts
     * @param int $decayMinutes Time window in minutes
     * @return mixed
     */
    public function handle(RequestContract $request, \Closure $next, int $maxAttempts = 60, int $decayMinutes = 1)
    {
        // Throttling logic using $maxAttempts and $decayMinutes
        
        return $next($request);
    }
}
```

## Middleware Groups

For convenience, you may want to group related middleware under a single key. This allows you to assign multiple middleware to a route with a single reference:

```php
// bootstrap/app.php

// ...

// Register middleware groups
$app->middlewareGroups([
    'web' => [
        \App\Middleware\EncryptCookies::class,
        \App\Middleware\StartSession::class,
        \App\Middleware\VerifyCsrfToken::class,
    ],
    'api' => [
        \App\Middleware\ThrottleRequestsMiddleware::class.':60,1',
        \App\Middleware\ForceJsonResponse::class,
    ],
]);

// ...
```

Then, you can apply the middleware group to a route:

```php
// routes/web.php

Route::get('/dashboard', 'DashboardController@index')->middleware('web');
```

## Before & After Middleware

As we've seen in the middleware structure, middleware can execute code before and after the request is handled by the application. This allows for powerful control over the request and response lifecycle.

### Before Middleware

A "before" middleware performs its task before the request is handled by the application:

```php
<?php

namespace App\Middleware;

use LightWeight\Http\Contracts\MiddlewareContract;
use LightWeight\Http\Contracts\RequestContract;

class BeforeMiddleware implements MiddlewareContract
{
    public function handle(RequestContract $request, \Closure $next)
    {
        // Perform task before the request is handled
        
        return $next($request);
    }
}
```

### After Middleware

An "after" middleware performs its task after the request is handled:

```php
<?php

namespace App\Middleware;

use LightWeight\Http\Contracts\MiddlewareContract;
use LightWeight\Http\Contracts\RequestContract;

class AfterMiddleware implements MiddlewareContract
{
    public function handle(RequestContract $request, \Closure $next)
    {
        $response = $next($request);
        
        // Perform task after the request is handled
        
        return $response;
    }
}
```

## Middleware Terminable

Some middleware may need to perform tasks after the HTTP response has been sent to the browser. For this purpose, LightWeight provides a `TerminableMiddlewareContract` that you can implement:

```php
<?php

namespace App\Middleware;

use LightWeight\Http\Contracts\MiddlewareContract;
use LightWeight\Http\Contracts\RequestContract;
use LightWeight\Http\Contracts\ResponseContract;
use LightWeight\Http\Contracts\TerminableMiddlewareContract;

class TerminableMiddleware implements MiddlewareContract, TerminableMiddlewareContract
{
    public function handle(RequestContract $request, \Closure $next)
    {
        return $next($request);
    }
    
    public function terminate(RequestContract $request, ResponseContract $response)
    {
        // Perform task after the response has been sent to the browser
    }
}
```

## Common Use Cases

### Authentication Middleware

```php
<?php

namespace App\Middleware;

use LightWeight\Auth\Auth;
use LightWeight\Http\Contracts\MiddlewareContract;
use LightWeight\Http\Contracts\RequestContract;

class AuthMiddleware implements MiddlewareContract
{
    public function handle(RequestContract $request, \Closure $next)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }
        
        return $next($request);
    }
}
```

### CORS Middleware

```php
<?php

namespace App\Middleware;

use LightWeight\Http\Contracts\MiddlewareContract;
use LightWeight\Http\Contracts\RequestContract;

class CorsMiddleware implements MiddlewareContract
{
    public function handle(RequestContract $request, \Closure $next)
    {
        $response = $next($request);
        
        $response->header('Access-Control-Allow-Origin', '*');
        $response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        
        return $response;
    }
}
```

### Logging Middleware

```php
<?php

namespace App\Middleware;

use LightWeight\Http\Contracts\MiddlewareContract;
use LightWeight\Http\Contracts\RequestContract;
use LightWeight\Log\LogFacade as Log;

class LoggingMiddleware implements MiddlewareContract
{
    public function handle(RequestContract $request, \Closure $next)
    {
        Log::info('Incoming request', [
            'method' => $request->getMethod(),
            'uri' => $request->getUri(),
            'ip' => $request->getClientIp(),
        ]);
        
        $response = $next($request);
        
        Log::info('Outgoing response', [
            'status' => $response->getStatusCode(),
        ]);
        
        return $response;
    }
}
```

## Middleware Order

The order in which middleware is applied is important. For example, a session middleware should be run before an authentication middleware that uses the session. The order is determined by the order in which the middleware is listed in your application's middleware stack.

## Controller Middleware

You can also apply middleware directly in your controllers:

```php
<?php

namespace App\Controllers;

use LightWeight\Http\ControllerBase;

class UserController extends ControllerBase
{
    public function __construct()
    {
        // Apply middleware to all methods in this controller
        $this->middleware('auth');
        
        // Apply middleware only to specific methods
        $this->middleware('throttle:60,1')->only(['store', 'update']);
        
        // Apply middleware to all methods except the specified ones
        $this->middleware('cache')->except(['store', 'update', 'destroy']);
    }
    
    // Controller methods...
}
```

## Conclusion

Middleware is a powerful feature of LightWeight that allows you to filter HTTP requests entering your application and modify HTTP responses leaving your application. By understanding how to create, register, and use middleware, you can add robust security, logging, and other features to your application.

## Related Topics

- [Routing Guide](routing-guide.md)
- [Controllers Guide](controllers-guide.md)
- [Authentication Guide](authentication-guide.md)
