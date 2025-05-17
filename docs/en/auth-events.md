# Authentication Events

LightWeight's authentication system triggers several events that you can use to implement custom functionality when a user attempts to authenticate, logs in, or logs out.

## Available Events

The following events are available in the authentication system:

| Event | Class | Description |
|--------|-------|-------------|
| `auth.attempt` | `AuthAttemptEvent` | Triggered when a user attempts to authenticate. Fires twice: before validation (with `successful=false`) and after (with the actual result). |
| `auth.login` | `AuthLoginEvent` | Triggered when a user logs in successfully. |
| `auth.logout` | `AuthLogoutEvent` | Triggered when a user logs out. |

## Listening for Authentication Events

### Using Closures

You can listen for authentication events using closures and the `on()` helper function:

```php
// Listen for authentication attempts
on('auth.attempt', function ($event) {
    $credentials = $event->getCredentials();
    $isSuccessful = $event->isSuccessful();
    $isRemembered = $event->isRemembered();
    
    // Log login attempts
    app('log')->info('Login attempt for ' . $credentials['email'] . 
                     ', result: ' . ($isSuccessful ? 'successful' : 'failed'));
    
    // Detect multiple failed attempts (security)
    if (!$isSuccessful) {
        $attempts = cache()->increment('login_attempts:' . $credentials['email'], 1);
        if ($attempts > 5) {
            cache()->set('login_blocked:' . $credentials['email'], true, 3600); // Block for 1 hour
        }
    } else {
        // Clear attempt counter if successful
        cache()->delete('login_attempts:' . $credentials['email']);
    }
});

// Listen for successful login
on('auth.login', function ($event) {
    $user = $event->getUser();
    $isRemembered = $event->isRemembered();
    
    // Update last login timestamp
    $user->last_login_at = now();
    $user->save();
    
    // Log activity
    app('log')->info('User ' . $user->email . ' has logged in');
});

// Listen for logout
on('auth.logout', function ($event) {
    $user = $event->getUser();
    
    // Log logout
    app('log')->info('User ' . $user->email . ' has logged out');
});
```

### Using Event Subscribers

For cleaner organization, you can create an authentication event subscriber:

```php
<?php

namespace App\Events\Subscribers;

use LightWeight\Events\AuthAttemptEvent;
use LightWeight\Events\AuthLoginEvent;
use LightWeight\Events\AuthLogoutEvent;
use LightWeight\Events\Contracts\SubscriberInterface;

class AuthEventSubscriber implements SubscriberInterface
{
    public function getEvents(): array
    {
        return [
            'auth.attempt' => 'onAuthAttempt',
            'auth.login' => 'onAuthLogin',
            'auth.logout' => 'onAuthLogout'
        ];
    }
    
    public function onAuthAttempt(AuthAttemptEvent $event)
    {
        $credentials = $event->getCredentials();
        $isSuccessful = $event->isSuccessful();
        
        // Implement your logic here
    }
    
    public function onAuthLogin(AuthLoginEvent $event)
    {
        $user = $event->getUser();
        $isRemembered = $event->isRemembered();
        
        // Implement your logic here
    }
    
    public function onAuthLogout(AuthLogoutEvent $event)
    {
        $user = $event->getUser();
        
        // Implement your logic here
    }
}
```

Then, register the subscriber in your `EventServiceProvider`:

```php
public function register(): void
{
    // Register event subscribers
    $this->registerSubscribers([
        // Other subscribers
        \App\Events\Subscribers\AuthEventSubscriber::class,
    ]);
}
```

## Common Use Cases

### Activity Logging

```php
on('auth.login', function (AuthLoginEvent $event) {
    ActivityLog::create([
        'user_id' => $event->getUser()->id,
        'type' => 'login',
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent()
    ]);
});
```

### Notifications

```php
on('auth.login', function (AuthLoginEvent $event) {
    $user = $event->getUser();
    
    // Notify about login from a new device
    if (isNewDevice($user, request()->userAgent())) {
        $user->notify(new NewDeviceLogin(
            request()->ip(),
            request()->userAgent()
        ));
    }
});
```

### Security

```php
on('auth.attempt', function (AuthAttemptEvent $event) {
    if (!$event->isSuccessful()) {
        $ip = request()->ip();
        $attempts = cache()->increment('failed_attempts:' . $ip, 1, 3600);
        
        if ($attempts > 10) {
            // Add IP to blacklist or implement CAPTCHA
            app('firewall')->blockIp($ip, 24); // Block for 24 hours
        }
    }
});
```

## Integration with External Services

```php
on('auth.login', function (AuthLoginEvent $event) {
    // Integrate with analytics system
    Analytics::track('user_login', [
        'user_id' => $event->getUser()->id,
        'timestamp' => now(),
        'location' => getLocationFromIp(request()->ip())
    ]);
});
```
