# Named Routes in LightWeight

LightWeight allows you to assign names to routes to make URL generation easier, which is especially useful when the route structure changes but the names remain the same.

## Assigning Names to Routes

You can assign a name to any route using the `setName()` method:

```php
Route::get('/users/{id}/profile', function($id) {
    // Code to show the profile
})->setName('user.profile');
```

## Generating URLs from Names

### `route()` Helper

The simplest way to generate a URL is with the `route()` helper:

```php
// Relative URL: /users/123/profile
$url = route('user.profile', ['id' => 123]);

// Absolute URL: http://example.com/users/123/profile
$absoluteUrl = route('user.profile', ['id' => 123], true);

// Absolute URL with custom domain: https://my-site.com/users/123/profile
$customUrl = route('user.profile', ['id' => 123], true, 'https://my-site.com');
```

### `Route` Class

You can also use static methods on the `Route` class:

```php
// Relative URL
$url = Route::url('user.profile', ['id' => 123]);

// Absolute URL
$absoluteUrl = Route::urlAbsolute('user.profile', ['id' => 123]);

// Absolute URL with custom domain
$customUrl = Route::urlAbsolute('user.profile', ['id' => 123], 'https://my-site.com');
```

### `Router` Class

If you have access to a `Router` instance, you can use the following methods:

```php
// Relative URL
$url = $router->generateUrl('user.profile', ['id' => 123]);

// Absolute URL
$absoluteUrl = $router->generateAbsoluteUrl('user.profile', ['id' => 123]);

// Absolute URL with custom domain
$customUrl = $router->generateAbsoluteUrl('user.profile', ['id' => 123], 'https://my-site.com');
```

## Getting a Route by Name

If you need to access route details:

```php
// Through Router
$route = $router->getRouteByName('user.profile');

// Through helper
$route = getRouteByName('user.profile');
```

## Parameter Validation

The system automatically checks that all required parameters are provided. If any are missing, an `InvalidArgumentException` will be thrown.

```php
// This will throw an exception because the 'id' parameter is missing
$url = route('user.profile');
```

## Default Domain Configuration

The default domain for absolute URLs is obtained from the `app.url` configuration. You can set it in `config/app.php`:

```php
return [
    'url' => 'https://my-application.com',
    // other configurations...
];
```
