# Sistema de Autenticación en LightWeight

## Introducción

El sistema de autenticación de LightWeight proporciona un mecanismo simple pero flexible para gestionar la autenticación de usuarios en tu aplicación. Está diseñado para ser fácil de implementar y personalizar según tus necesidades específicas.

## Estructura de Autenticación

El sistema de autenticación de LightWeight se compone de los siguientes elementos clave:

1. **Clase `Auth`**: Proporciona métodos estáticos para acceder al usuario autenticado y verificar el estado de autenticación.
2. **Clase `Authenticatable`**: Una clase base que extiende el Model de ORM y proporciona funcionalidad de autenticación al usuario.
3. **Interfaz `AuthenticatorContract`**: Define los métodos que debe implementar cualquier autenticador.
4. **Implementaciones de Autenticador**: Clases que implementan la lógica de autenticación (por ejemplo, `SessionAuthenticator`).

## Configuración Básica

### Archivo de Configuración

LightWeight utiliza un archivo de configuración simple para la autenticación en `config/auth.php`:

```php
<?php

return [
    'method' => 'session', // Método de autenticación (session, jwt, etc.)
    'jwt_options' => [     // Opciones para el método JWT (si se utiliza)
        'digest_alg' => 'HS256',
        'max_age' => 3600,
        'leeway' => 60,
    ],
    'model' => \App\Models\User::class,  // Modelo que representa a los usuarios
    'password_field' => 'password',      // Campo que almacena la contraseña
    'username_field' => 'email',         // Campo que almacena el nombre de usuario/email
];
```

### Modelo de Usuario

Tu modelo User debe extender la clase `Authenticatable`:

```php
<?php

namespace App\Models;

use LightWeight\Auth\Authenticatable;

class User extends Authenticatable
{
    protected ?string $table = 'users';
    protected array $fillable = ['name', 'email', 'password'];
    protected array $hidden = ['password'];
    
    // Puedes añadir métodos y propiedades adicionales
}
```

## Uso Básico

### Verificar Estado de Autenticación

```php
// Verificar si un usuario está autenticado
if (Auth::check()) {
    // El usuario está autenticado
}

// Obtener el usuario autenticado
$user = Auth::user();

// Obtener una propiedad específica del usuario
$email = Auth::user()->email;
```

### Autenticar Usuarios

```php
// Intentar autenticar a un usuario
$credentials = [
    'email' => 'usuario@ejemplo.com',
    'password' => 'secreto'
];

if (Auth::attempt($credentials)) {
    // Autenticación exitosa
    return redirect('/dashboard');
} else {
    // Autenticación fallida
    return back()->withErrors(['login' => 'Credenciales inválidas']);
}
```

### Autenticar con Funcionalidad "Recordarme"

```php
// El segundo parámetro indica si se debe "recordar" al usuario
if (Auth::attempt($credentials, true)) {
    // El usuario será recordado por un período más largo
}
```

### Cerrar Sesión

```php
// Cerrar sesión del usuario actual
Auth::logout();

return redirect('/login');
```

## Proteger Rutas

Puedes proteger rutas utilizando el middleware `auth`:

```php
// routes.php
Route::get('/perfil', 'ProfileController@show')->middleware('auth');

// O para un grupo de rutas
Route::group(['middleware' => 'auth'], function() {
    Route::get('/dashboard', 'DashboardController@show');
    Route::get('/configuracion', 'SettingsController@show');
});
```

## Crear Autenticadores Personalizados

Puedes crear tus propios métodos de autenticación implementando el `AuthenticatorContract`:

```php
<?php

namespace App\Auth;

use LightWeight\Auth\Contracts\AuthenticatorContract;
use LightWeight\Auth\Authenticatable;

class CustomAuthenticator implements AuthenticatorContract
{
    public function check(): bool
    {
        // Verificar si el usuario está autenticado
    }
    
    public function user(): ?Authenticatable
    {
        // Devolver el usuario autenticado o null
    }
    
    public function attempt(array $credentials, bool $remember = false): bool
    {
        // Intentar autenticar al usuario
    }
    
    public function login(Authenticatable $user, bool $remember = false): void
    {
        // Iniciar sesión para el usuario
    }
    
    public function logout(): void
    {
        // Cerrar sesión del usuario
    }
}
```

Luego registra tu autenticador personalizado en un proveedor de servicios:

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

## Autenticación JWT

LightWeight también proporciona autenticación JWT (JSON Web Token) de forma nativa:

### Configuración

```php
// config/auth.php
return [
    'method' => 'jwt',
    'jwt_options' => [
        'digest_alg' => 'HS256',
        'max_age' => 3600,         // Tiempo de expiración del token en segundos
        'leeway' => 60,             // Margen para la validación de expiración del token
        'secret' => env('JWT_SECRET', 'tu-clave-secreta')
    ],
    // ...
];
```

### Uso en Rutas API

```php
// Autenticar y obtener un token
Route::post('/api/login', function() {
    $credentials = request()->only('email', 'password');
    
    if (!Auth::attempt($credentials)) {
        return response()->json(['error' => 'Credenciales inválidas'], 401);
    }
    
    $token = Auth::token();
    
    return response()->json(['token' => $token]);
});

// Rutas API protegidas
Route::group(['middleware' => 'auth:jwt', 'prefix' => 'api'], function() {
    Route::get('/user', function() {
        return Auth::user();
    });
});
```

## Eventos

El sistema de autenticación podría integrarse con los siguientes eventos (nota: actualmente en desarrollo y no disponibles en la versión actual):

- `auth.attempt`: Se dispararía cuando se realiza un intento de autenticación
- `auth.login`: Se dispararía cuando un usuario inicia sesión
- `auth.logout`: Se dispararía cuando un usuario cierra sesión
- `auth.failed`: Se dispararía cuando falla la autenticación

> **Nota importante**: Estos eventos están planificados para futuras versiones del framework, pero **no están implementados actualmente**. Si necesitas funcionalidad similar, puedes implementar tu propio sistema de eventos para la autenticación.

Ejemplo de implementación futura:

```php
// EJEMPLO SOLO PARA REFERENCIA - NO IMPLEMENTADO ACTUALMENTE
on('auth.login', function($event) {
    $user = $event->getData()['user'];
    // Registrar la actividad de inicio de sesión
    app('log')->info("El usuario {$user->id} ha iniciado sesión");
});
```

## Mejores Prácticas

1. **Nunca almacenes contraseñas en texto plano**: Siempre hashea las contraseñas antes de almacenarlas.
2. **Usa HTTPS**: Siempre usa HTTPS para las rutas de autenticación.
3. **Utiliza un hash de contraseña seguro**: LightWeight usa Bcrypt por defecto.
4. **Habilita la protección CSRF**: Usa tokens CSRF para tus formularios de login.
5. **Implementa limitación de intentos**: Protege tus endpoints de autenticación contra ataques de fuerza bruta.
6. **Considera la autenticación multi-factor**: Para seguridad adicional.
7. **Usa configuraciones de seguridad de sesión adecuadas**: Establece cookies seguras, solo HTTP.
8. **Implementa la regeneración adecuada de sesiones**: Regenera los IDs de sesión después del inicio de sesión.

## Recursos Adicionales

- [Guía de Middleware](middleware-guide.md)
- [Gestión de Sesiones](session-management.md)
- [Mejores Prácticas de Seguridad](security-best-practices.md)
