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
];
```

### Modelo de Usuario

Para crear un modelo de usuario autenticable, simplemente extiende la clase `Authenticatable`:

```php
<?php

namespace App\Models;

use LightWeight\Auth\Authenticatable;

class User extends Authenticatable
{
    protected $table = 'users';
    
    protected $fillable = [
        'name',
        'email',
        'password',
    ];
}
```

## Uso del Sistema de Autenticación

### Verificación de Usuario Autenticado

Para comprobar si un usuario está autenticado:

```php
// Usando la clase Auth
if (Auth::isGuest()) {
    // Usuario no autenticado
    echo "Por favor inicia sesión";
} else {
    // Usuario autenticado
    echo "Bienvenido, " . Auth::user()->name;
}
```

### Acceso al Usuario Actual

```php
// Obtener el usuario actual autenticado
$user = Auth::user();

// Si el usuario no está autenticado, devolverá null
if ($user) {
    echo "ID: " . $user->id();
    echo "Nombre: " . $user->name;
    echo "Email: " . $user->email;
}
```

### Login y Logout

Para iniciar y cerrar sesión de un usuario:

```php
// Obtener el usuario por ID, email, etc.
$user = User::find(1); // o cualquier otro método para obtener el usuario

// Iniciar sesión
$user->login();

// Verificar si el usuario está autenticado
if ($user->isAuthenticated()) {
    echo "Autenticación correcta";
}

// Cerrar sesión
$user->logout();
```

## Implementaciones de Autenticación

LightWeight soporta diferentes métodos de autenticación a través de las implementaciones de `AuthenticatorContract`. El método predeterminado es la autenticación basada en sesiones.

### Autenticación por Sesión

La implementación `SessionAuthenticator` utiliza la sesión PHP para almacenar la información del usuario autenticado:

```php
<?php

namespace LightWeight\Auth\Authenticators;

use LightWeight\Auth\Contracts\Authenticators\AuthenticatorContract;
use LightWeight\Auth\Authenticatable;

class SessionAuthenticator implements AuthenticatorContract
{
    public function login(Authenticatable $authenticatable)
    {
        session()->set('_auth', $authenticatable);
    }
    
    public function logout(Authenticatable $authenticatable)
    {
        session()->remove("_auth");
    }
    
    public function isAuthenticated(Authenticatable $authenticatable): bool
    {
        return session()->get("_auth")?->id() === $authenticatable->id();
    }
    
    public function resolve(): ?Authenticatable
    {
        return session()->get("_auth");
    }
}
```

## Protección de Rutas

### Middleware de Autenticación

Para proteger rutas que requieren autenticación, se puede crear un middleware similar al siguiente:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use LightWeight\Auth\Auth;
use LightWeight\Http\Request;
use LightWeight\Http\Response;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::isGuest()) {
            return redirect('/login');
        }
        
        return $next($request);
    }
}
```

### Uso del Middleware

Aplica el middleware a tus rutas:

```php
// Proteger una ruta individual
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->setMiddlewares([AuthMiddleware::class]);

// Proteger un grupo de rutas
Route::$prefix = '/account';
Route::setGroupMiddlewares([AuthMiddleware::class]);

Route::get('/profile', [ProfileController::class, 'show']);
Route::get('/settings', [SettingsController::class, 'index']);

// Restaurar la configuración
Route::$prefix = '';
Route::setGroupMiddlewares([]);
```

## Middleware para Invitados

Para restringir rutas solo a usuarios no autenticados (como páginas de inicio de sesión y registro):

```php
<?php

namespace App\Http\Middleware;

use Closure;
use LightWeight\Auth\Auth;
use LightWeight\Http\Request;
use LightWeight\Http\Response;

class GuestMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::isGuest()) {
            return redirect('/dashboard');
        }
        
        return $next($request);
    }
}
```

### Uso del Middleware para Invitados

```php
Route::get('/login', [AuthController::class, 'showLogin'])
    ->setMiddlewares([GuestMiddleware::class]);
Route::get('/register', [AuthController::class, 'showRegister'])
    ->setMiddlewares([GuestMiddleware::class]);
```

## Ejemplo de Implementación Completa

A continuación se muestra un ejemplo de implementación completa de un sistema de autenticación en una aplicación LightWeight:

### Controlador de Autenticación

```php
<?php

namespace App\Controllers\Auth;

use LightWeight\Http\ControllerBase;
use LightWeight\Http\Request;
use LightWeight\Http\Response;
use App\Models\User;

class AuthController extends ControllerBase
{
    public function showLogin()
    {
        return view('auth.login');
    }
    
    public function login(Request $request)
    {
        $email = $request->data('email');
        $password = $request->data('password');
        
        $user = User::where('email', $email)->first();
        
        if ($user && password_verify($password, $user->password)) {
            $user->login();
            return redirect('/dashboard');
        }
        
        return back()->with('error', 'Credenciales incorrectas');
    }
    
    public function logout()
    {
        if (!Auth::isGuest()) {
            Auth::user()->logout();
        }
        
        return redirect('/login');
    }
    
    public function showRegister()
    {
        return view('auth.register');
    }
    
    public function register(Request $request)
    {
        // Validación
        $errors = [];
        
        if (!$request->data('name')) {
            $errors['name'] = 'El nombre es obligatorio';
        }
        
        if (!filter_var($request->data('email'), FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'El email no es válido';
        }
        
        if (strlen($request->data('password')) < 6) {
            $errors['password'] = 'La contraseña debe tener al menos 6 caracteres';
        }
        
        if ($request->data('password') !== $request->data('password_confirmation')) {
            $errors['password_confirmation'] = 'Las contraseñas no coinciden';
        }
        
        if (!empty($errors)) {
            return back()->with('errors', $errors)->withInput();
        }
        
        // Crear usuario
        $user = new User();
        $user->name = $request->data('name');
        $user->email = $request->data('email');
        $user->password = password_hash($request->data('password'), PASSWORD_DEFAULT);
        $user->save();
        
        // Iniciar sesión
        $user->login();
        
        return redirect('/dashboard');
    }
}
```

### Rutas de Autenticación

```php
<?php

// Rutas de invitados
Route::get('/login', [AuthController::class, 'showLogin'])
    ->setMiddlewares([GuestMiddleware::class]);
Route::post('/login', [AuthController::class, 'login'])
    ->setMiddlewares([GuestMiddleware::class]);
Route::get('/register', [AuthController::class, 'showRegister'])
    ->setMiddlewares([GuestMiddleware::class]);
Route::post('/register', [AuthController::class, 'register'])
    ->setMiddlewares([GuestMiddleware::class]);

// Rutas protegidas
Route::get('/logout', [AuthController::class, 'logout'])
    ->setMiddlewares([AuthMiddleware::class]);
Route::$prefix = '/dashboard';
Route::setGroupMiddlewares([AuthMiddleware::class]);
Route::get('/', [DashboardController::class, 'index']);
Route::get('/profile', [ProfileController::class, 'show']);
Route::$prefix = '';
Route::setGroupMiddlewares([]);
```

## Notas importantes

1. **Seguridad de contraseñas**: Siempre utiliza `password_hash()` con `PASSWORD_DEFAULT` para almacenar contraseñas y `password_verify()` para verificarlas.

2. **Autenticadores personalizados**: Puedes crear tus propios autenticadores implementando la interfaz `AuthenticatorContract` para manejar diferentes métodos de autenticación (JWT, tokens API, etc.).

3. **Configuración del autenticador**: El método de autenticación que se utiliza está definido en `config/auth.php` y se carga automáticamente mediante el contenedor de dependencias de LightWeight.

4. **Seguridad**: Asegúrate de proteger todas las rutas que requieran autenticación con el middleware adecuado.

## Extensión del Sistema

LightWeight proporciona un sistema de autenticación base que puedes extender fácilmente para agregar funcionalidades como:

1. **Verificación de email**
2. **Recuperación de contraseña**
3. **Autenticación de dos factores**
4. **Autenticación por redes sociales**
5. **Sistema de roles y permisos**

Estas extensiones se pueden implementar creando los controladores, middlewares y vistas correspondientes según las necesidades específicas de tu aplicación.
