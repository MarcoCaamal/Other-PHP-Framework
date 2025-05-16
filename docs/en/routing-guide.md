# Routing System in LightWeight

## Introduction

The routing system is a central component of LightWeight that allows you to define your application routes, connecting URLs with the controllers and actions that should handle them. The router determines what code runs when a user visits a specific URL, facilitating the organization of your application in a clear and maintainable structure.

## Basic Routes

### Defining Routes

Routes in LightWeight are typically defined in the `routes/web.php` file. Each route consists of an HTTP method, a URI, and an action that will be executed when that route is accessed.

```php
use LightWeight\Routing\Route;
use App\Controllers\HomeController;

// Basic route with an anonymous function
Route::get('/', function() {
    return view('welcome');
});

// Route directed to a controller method
Route::get('/users', [UserController::class, 'index']);
```

### Available HTTP Methods

LightWeight provides methods for the most common HTTP verbs:

```php
Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::patch('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);

// To respond to multiple HTTP verbs
Route::match(['get', 'post'], '/users/search', [UserController::class, 'search']);

// To respond to any HTTP verb
Route::any('/users/status', [UserController::class, 'status']);
```

### Redirects

To create simple redirects:

```php
// Permanent redirect (301)
Route::redirect('/old-url', '/new-url', 301);

// Temporary redirect (302, default)
Route::redirect('/temporary', '/new-location');
```

## Route Parameters

### Required Parameters

You can define route parameters using curly braces:

```php
Route::get('/users/{id}', function($id) {
    return 'User: ' . $id;
});

// With controller
Route::get('/users/{id}', [UserController::class, 'show']);
```

### Optional Parameters

Optional parameters are defined with a question mark and must be at the end of the URI:

```php
Route::get('/users/{name?}', function($name = 'Guest') {
    return 'User: ' . $name;
});
```

### Parameter Constraints

You can add constraints to parameters using regular expressions:

```php
Route::get('/users/{id}', [UserController::class, 'show'])
    ->where('id', '[0-9]+');

// Multiple constraints
Route::get('/posts/{post}/comments/{comment}', [PostController::class, 'showComment'])
    ->where([
        'post' => '[0-9]+',
        'comment' => '[a-z0-9\-]+'
    ]);
```

### Global Parameter Constraints

You can define global parameter patterns in your `RouteServiceProvider`:

```php
public function boot()
{
    Route::pattern('id', '[0-9]+');
    Route::pattern('username', '[a-z0-9_-]+');
}
```

## Named Routes

Named routes provide an easy way to generate URLs or redirects:

```php
Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');

// Using named routes
$url = route('users.show', ['id' => 1]);

// Redirect to named route
return redirect()->route('users.show', ['id' => 1]);
```

## Route Groups

Route groups allow you to share attributes across multiple routes:

### Middleware

Apply middleware to a group of routes:

```php
Route::middleware(['auth', 'verified'])->group(function() {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/profile', [ProfileController::class, 'show']);
});
```

### Prefix

Add a prefix to all routes in a group:

```php
Route::prefix('admin')->group(function() {
    Route::get('/users', [AdminController::class, 'users']);
    Route::get('/settings', [AdminController::class, 'settings']);
});
```

### Name Prefix

Add a name prefix to all named routes in a group:

```php
Route::name('admin.')->group(function() {
    Route::get('/users', [AdminController::class, 'users'])->name('users'); // Result: admin.users
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings'); // Result: admin.settings
});
```

### Namespace

Group routes under a common namespace:

```php
Route::namespace('App\\Controllers\\Admin')->group(function() {
    // Controllers will be in App\Controllers\Admin
    Route::get('/users', [UserController::class, 'index']);
});
```

### Combining Group Attributes

You can combine different group attributes:

```php
Route::prefix('api')
    ->middleware('api')
    ->namespace('App\\Controllers\\Api')
    ->name('api.')
    ->group(function() {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
    });
```

## Route Model Binding

### Implicit Binding

LightWeight can automatically resolve models from route parameters:

```php
Route::get('/users/{user}', function(User $user) {
    // $user is automatically resolved from the database
    return $user;
});
```

### Customizing the Key

By default, the model is resolved using its primary key, but you can customize this:

```php
// In the model
public function getRouteKey()
{
    return $this->slug;
}
```

### Explicit Binding

You can define explicit model bindings in the `RouteServiceProvider`:

```php
public function boot()
{
    Route::bind('user', function($value) {
        return User::where('username', $value)->firstOrFail();
    });
}
```

## Fallback Routes

Create a fallback route for 404 handling:

```php
Route::fallback(function() {
    return response()->view('errors.404', [], 404);
});
```

## Form Method Spoofing

HTML forms only support GET and POST methods. To use other methods, include a `_method` field:

```html
<form method="POST" action="/users/1">
    <input type="hidden" name="_method" value="DELETE">
    <!-- or -->
    @method('DELETE')
</form>
```

## Accessing the Current Route

You can access information about the current route:

```php
$route = request()->route();
$name = $route->getName();
$action = $route->getAction();
$parameters = $route->parameters();
```

## CSRF Protection

LightWeight protects your application from cross-site request forgeries:

```html
<form method="POST" action="/profile">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <!-- or -->
    @csrf
</form>
```

To exclude routes from CSRF protection, add them to the `$except` array in `App\Http\Middleware\VerifyCsrfToken`.

## API Routes

API routes are typically defined in `routes/api.php`:

```php
Route::prefix('api/v1')->group(function() {
    Route::get('/users', [ApiController::class, 'getUsers']);
    Route::post('/auth/login', [AuthController::class, 'login']);
});
```

## Route Caching

For production environments, you can cache your routes for better performance:

```bash
php light route:cache
```

To clear the route cache:

```bash
php light route:clear
```

## Best Practices

1. **Keep route definitions clean**: Use controllers instead of closure routes for complex logic.
2. **Use resource routes** for CRUD operations.
3. **Name your routes** for easier reference.
4. **Group related routes** to keep your code organized.
5. **Use middleware wisely** to filter requests.
6. **Consider versioning for APIs** using prefixes.

## Route Debugging

To list all registered routes:

```bash
php light route:list
```

## Conclusion

The LightWeight routing system provides a flexible and powerful way to define the entry points of your application. By using named routes, route groups, and model binding, you can build clean and maintainable applications.

For more information on related topics, check out:
- [Controller Guide](controllers-guide.md)
- [Middleware Guide](middleware-guide.md)
- [Request and Response Handling](request-response-handling.md)
