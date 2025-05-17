# Eventos de Autenticación

El sistema de autenticación de LightWeight dispara varios eventos que puedes utilizar para implementar funcionalidades personalizadas cuando un usuario intenta autenticarse, inicia sesión o cierra sesión.

## Eventos Disponibles

Los siguientes eventos están disponibles en el sistema de autenticación:

| Evento | Clase | Descripción |
|--------|-------|-------------|
| `auth.attempt` | `AuthAttemptEvent` | Disparado cuando un usuario intenta autenticarse. Se dispara dos veces: antes de la validación (con `successful=false`) y después (con el resultado real). |
| `auth.login` | `AuthLoginEvent` | Disparado cuando un usuario inicia sesión exitosamente. |
| `auth.logout` | `AuthLogoutEvent` | Disparado cuando un usuario cierra sesión. |

## Escuchar Eventos de Autenticación

### Usando Closures

Puedes escuchar los eventos de autenticación utilizando closures y la función helper `on()`:

```php
// Escucha intentos de autenticación
on('auth.attempt', function ($event) {
    $credentials = $event->getCredentials();
    $isSuccessful = $event->isSuccessful();
    $isRemembered = $event->isRemembered();
    
    // Registrar intentos de login
    app('log')->info('Intento de login para ' . $credentials['email'] . 
                     ', resultado: ' . ($isSuccessful ? 'exitoso' : 'fallido'));
    
    // Detectar múltiples intentos fallidos (seguridad)
    if (!$isSuccessful) {
        $attempts = cache()->increment('login_attempts:' . $credentials['email'], 1);
        if ($attempts > 5) {
            cache()->set('login_blocked:' . $credentials['email'], true, 3600); // Bloqueo por 1 hora
        }
    } else {
        // Limpiar contador de intentos si es exitoso
        cache()->delete('login_attempts:' . $credentials['email']);
    }
});

// Escucha inicio de sesión exitoso
on('auth.login', function ($event) {
    $user = $event->getUser();
    $isRemembered = $event->isRemembered();
    
    // Actualizar último inicio de sesión
    $user->last_login_at = now();
    $user->save();
    
    // Registrar actividad
    app('log')->info('Usuario ' . $user->email . ' ha iniciado sesión');
});

// Escucha cierre de sesión
on('auth.logout', function ($event) {
    $user = $event->getUser();
    
    // Registrar cierre de sesión
    app('log')->info('Usuario ' . $user->email . ' ha cerrado sesión');
});
```

### Usando Suscriptores de Eventos

Para una organización más limpia, puedes crear un suscriptor de eventos de autenticación:

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
        
        // Implementa tu lógica aquí
    }
    
    public function onAuthLogin(AuthLoginEvent $event)
    {
        $user = $event->getUser();
        $isRemembered = $event->isRemembered();
        
        // Implementa tu lógica aquí
    }
    
    public function onAuthLogout(AuthLogoutEvent $event)
    {
        $user = $event->getUser();
        
        // Implementa tu lógica aquí
    }
}
```

Luego, registra el suscriptor en tu `EventServiceProvider`:

```php
public function register(): void
{
    // Registrar suscriptores de eventos
    $this->registerSubscribers([
        // Otros suscriptores
        \App\Events\Subscribers\AuthEventSubscriber::class,
    ]);
}
```

## Casos de Uso Comunes

### Registro de Actividad

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

### Notificaciones

```php
on('auth.login', function (AuthLoginEvent $event) {
    $user = $event->getUser();
    
    // Notificar sobre inicio de sesión desde un nuevo dispositivo
    if (isNewDevice($user, request()->userAgent())) {
        $user->notify(new NewDeviceLogin(
            request()->ip(),
            request()->userAgent()
        ));
    }
});
```

### Seguridad

```php
on('auth.attempt', function (AuthAttemptEvent $event) {
    if (!$event->isSuccessful()) {
        $ip = request()->ip();
        $attempts = cache()->increment('failed_attempts:' . $ip, 1, 3600);
        
        if ($attempts > 10) {
            // Añadir IP a lista negra o implementar CAPTCHA
            app('firewall')->blockIp($ip, 24); // Bloquear por 24 horas
        }
    }
});
```

## Integración con Servicios Externos

```php
on('auth.login', function (AuthLoginEvent $event) {
    // Integrar con sistema de análisis
    Analytics::track('user_login', [
        'user_id' => $event->getUser()->id,
        'timestamp' => now(),
        'location' => getLocationFromIp(request()->ip())
    ]);
});
```
