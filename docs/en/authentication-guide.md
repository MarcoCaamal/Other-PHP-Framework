# Authentication System in LightWeight

## Introduction

The LightWeight authentication system provides a simple but flexible mechanism for managing user authentication in your application. It's designed to be easy to implement and customize according to your specific needs.

## Authentication Structure

The LightWeight authentication system consists of the following key elements:

1. **`Auth` Class**: Provides static methods to access the authenticated user and verify authentication status.
2. **`Authenticatable` Class**: A base class that extends the ORM Model and provides authentication functionality to the user.
3. **`AuthenticatorContract` Interface**: Defines the methods that any authenticator must implement.
4. **Authenticator Implementations**: Classes that implement authentication logic (e.g., `SessionAuthenticator`).

## Basic Configuration

### Configuration File

LightWeight uses a simple configuration file for authentication in `config/auth.php`:

```php
<?php

return [
    'method' => 'session', // Authentication method (session, jwt, etc.)
    'jwt_options' => [     // Options for JWT method (if used)
        'digest_alg' => 'HS256',
        'max_age' => 3600,
        'leeway' => 60,
    ],
    'model' => \App\Models\User::class,  // Model that represents users
    'password_field' => 'password',      // Field that stores the password
    'username_field' => 'email',         // Field that stores the username/email
];
```

### User Model

Your User model should extend the `Authenticatable` class:

```php
<?php

namespace App\Models;

use LightWeight\Auth\Authenticatable;

class User extends Authenticatable
{
    protected ?string $table = 'users';
    protected array $fillable = ['name', 'email', 'password'];
    protected array $hidden = ['password'];
    
    // You can add additional methods and properties
}
```

## Basic Usage

### Checking Authentication Status

```php
// Check if a user is authenticated
if (Auth::check()) {
    // The user is authenticated
}

// Get the authenticated user
$user = Auth::user();

// Get a specific user property
$email = Auth::user()->email;
```

### Authenticating Users

```php
// Attempt to authenticate a user
$credentials = [
    'email' => 'user@example.com',
    'password' => 'secret'
];

if (Auth::attempt($credentials)) {
    // Authentication successful
    return redirect('/dashboard');
} else {
    // Authentication failed
    return back()->withErrors(['login' => 'Invalid credentials']);
}
```

### Authenticating with Remember Me Functionality

```php
// The second parameter indicates whether to "remember" the user
if (Auth::attempt($credentials, true)) {
    // User will be remembered for a longer period
}
```

### Logging Out

```php
// Log out the current user
Auth::logout();

return redirect('/login');
```

## Protecting Routes

You can protect routes using the `auth` middleware:

```php
// routes.php
Route::get('/profile', 'ProfileController@show')->middleware('auth');

// Or for a group of routes
Route::group(['middleware' => 'auth'], function() {
    Route::get('/dashboard', 'DashboardController@show');
    Route::get('/settings', 'SettingsController@show');
});
```

## Creating Custom Authenticators

You can create your own authentication methods by implementing the `AuthenticatorContract`:

```php
<?php

namespace App\Auth;

use LightWeight\Auth\Contracts\AuthenticatorContract;
use LightWeight\Auth\Authenticatable;

class CustomAuthenticator implements AuthenticatorContract
{
    public function check(): bool
    {
        // Check if user is authenticated
    }
    
    public function user(): ?Authenticatable
    {
        // Return the authenticated user or null
    }
    
    public function attempt(array $credentials, bool $remember = false): bool
    {
        // Attempt to authenticate the user
    }
    
    public function login(Authenticatable $user, bool $remember = false): void
    {
        // Log in the user
    }
    
    public function logout(): void
    {
        // Log out the user
    }
}
```

Then register your custom authenticator in a service provider:

```php
<?php

namespace App\Providers;

use LightWeight\Providers\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            'LightWeight\Auth\Contracts\AuthenticatorContract',
            'App\Auth\CustomAuthenticator'
        );
    }
}
```

## JWT Authentication

LightWeight also provides JWT (JSON Web Token) authentication out of the box:

### Configuration

```php
// config/auth.php
return [
    'method' => 'jwt',
    'jwt_options' => [
        'digest_alg' => 'HS256',
        'max_age' => 3600,         // Token expiration time in seconds
        'leeway' => 60,             // Leeway for token expiration validation
        'secret' => env('JWT_SECRET', 'your-secret-key')
    ],
    // ...
];
```

### Usage in API Routes

```php
// Authenticate and get a token
Route::post('/api/login', function() {
    $credentials = request()->only('email', 'password');
    
    if (!Auth::attempt($credentials)) {
        return response()->json(['error' => 'Invalid credentials'], 401);
    }
    
    $token = Auth::token();
    
    return response()->json(['token' => $token]);
});

// Protected API routes
Route::group(['middleware' => 'auth:jwt', 'prefix' => 'api'], function() {
    Route::get('/user', function() {
        return Auth::user();
    });
});
```

## Events

The authentication system fires several events that you can listen for:

- `auth.attempt`: Fired when an authentication attempt is made
- `auth.login`: Fired when a user logs in
- `auth.logout`: Fired when a user logs out
- `auth.failed`: Fired when authentication fails

Example:

```php
on('auth.login', function($event) {
    $user = $event->getData()['user'];
    // Log the login activity
    app('log')->info("User {$user->id} logged in");
});
```

## Best Practices

1. **Never store passwords in plain text**: Always hash passwords before storing them.
2. **Use HTTPS**: Always use HTTPS for authentication routes.
3. **Use a secure password hash**: LightWeight uses Bcrypt by default.
4. **Enable CSRF protection**: Use CSRF tokens for your login forms.
5. **Implement rate limiting**: Protect your authentication endpoints from brute force attacks.
6. **Consider multi-factor authentication**: For additional security.
7. **Use proper session security settings**: Set secure, HTTP-only cookies.
8. **Implement proper session regeneration**: Regenerate session IDs after login.

## Additional Resources

- [Middleware Guide](middleware-guide.md)
- [Session Management](session-management.md)
- [Security Best Practices](security-best-practices.md)
