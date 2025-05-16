# Sistema de Enrutamiento en LightWeight

## Introducción

El sistema de enrutamiento es un componente central de LightWeight que permite definir las rutas de tu aplicación, conectando las URLs con los controladores y acciones que deben manejarlas. El enrutador determina qué código se ejecuta cuando un usuario visita una URL específica, facilitando la organización de tu aplicación en una estructura clara y mantenible.

## Rutas Básicas

### Definiendo Rutas

Las rutas en LightWeight se definen típicamente en el archivo `routes/web.php`. Cada ruta consta de un método HTTP, una URI y una acción que se ejecutará cuando se acceda a esa ruta.

```php
use LightWeight\Routing\Route;
use App\Controllers\HomeController;

// Ruta básica con una función anónima
Route::get('/', function() {
    return view('welcome');
});

// Ruta dirigida a un método de controlador
Route::get('/users', [UserController::class, 'index']);
```

### Métodos HTTP Disponibles

LightWeight proporciona métodos para los verbos HTTP más comunes:

```php
Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::patch('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);

// Para responder a múltiples verbos HTTP
Route::match(['get', 'post'], '/users/search', [UserController::class, 'search']);

// Para responder a cualquier verbo HTTP
Route::any('/users/status', [UserController::class, 'status']);
```

### Redirecciones

Para crear redirecciones simples:

```php
// Redirección permanente (301)
Route::redirect('/old-url', '/new-url', 301);

// Redirección temporal (302, por defecto)
Route::redirect('/temporary', '/new-location');
```

## Parámetros de Ruta

### Parámetros Requeridos

Puedes definir parámetros de ruta utilizando llaves:

```php
Route::get('/users/{id}', function($id) {
    return 'Usuario: ' . $id;
});

// Con controlador
Route::get('/users/{id}', [UserController::class, 'show']);
```

### Parámetros Opcionales

Los parámetros opcionales se definen con un signo de interrogación y deben estar al final de la URI:

```php
Route::get('/users/{name?}', function($name = 'Invitado') {
    return 'Usuario: ' . $name;
});
```

### Restricciones de Parámetros

Puedes añadir restricciones a los parámetros utilizando expresiones regulares:

```php
Route::get('/users/{id}', [UserController::class, 'show'])
    ->where('id', '[0-9]+');

// Múltiples restricciones
Route::get('/posts/{post}/comments/{comment}', [PostController::class, 'showComment'])
    ->where([
        'post' => '[0-9]+',
        'comment' => '[a-z0-9\-]+'
    ]);
```

### Restricciones Globales de Parámetros

Puedes definir patrones de parámetros globales en tu `RouteServiceProvider`:

```php
public function boot()
{
    Route::pattern('id', '[0-9]+');
    Route::pattern('username', '[a-z0-9_-]+');
}
```

## Rutas con Nombre

Las rutas con nombre proporcionan una forma fácil de generar URLs o redirecciones:

```php
Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');

// Usando rutas con nombre
$url = route('users.show', ['id' => 1]);

// Redirección a una ruta con nombre
return redirect()->route('users.show', ['id' => 1]);
```

## Grupos de Rutas

Los grupos de rutas permiten compartir atributos entre múltiples rutas:

### Middleware

Aplica middleware a un grupo de rutas:

```php
Route::middleware(['auth', 'verified'])->group(function() {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/profile', [ProfileController::class, 'show']);
});
```

### Prefijo

Añade un prefijo a todas las rutas en un grupo:

```php
Route::prefix('admin')->group(function() {
    Route::get('/users', [AdminController::class, 'users']);
    Route::get('/settings', [AdminController::class, 'settings']);
});
```

### Prefijo de Nombre

Añade un prefijo de nombre a todas las rutas con nombre en un grupo:

```php
Route::name('admin.')->group(function() {
    Route::get('/users', [AdminController::class, 'users'])->name('users'); // Resultado: admin.users
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings'); // Resultado: admin.settings
});
```

### Namespace

Agrupa rutas bajo un namespace común:

```php
Route::namespace('App\\Controllers\\Admin')->group(function() {
    // Los controladores estarán en App\Controllers\Admin
    Route::get('/users', [UserController::class, 'index']);
});
```

### Combinando Atributos de Grupo

Puedes combinar diferentes atributos de grupo:

```php
Route::prefix('api')
    ->middleware('api')
    ->namespace('App\\Controllers\\Api')
    ->name('api.')
    ->group(function() {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
    });
```

## Vinculación de Modelos en Rutas

### Vinculación Implícita

LightWeight puede resolver automáticamente modelos desde parámetros de ruta:

```php
Route::get('/users/{user}', function(User $user) {
    // $user se resuelve automáticamente desde la base de datos
    return $user;
});
```

### Personalizando la Clave

Por defecto, el modelo se resuelve utilizando su clave primaria, pero puedes personalizar esto:

```php
// En el modelo
public function getRouteKey()
{
    return $this->slug;
}
```

### Vinculación Explícita

Puedes definir vinculaciones de modelo explícitas en el `RouteServiceProvider`:

```php
public function boot()
{
    Route::bind('user', function($value) {
        return User::where('username', $value)->firstOrFail();
    });
}
```

## Rutas de Respaldo

Crea una ruta de respaldo para manejar errores 404:

```php
Route::fallback(function() {
    return response()->view('errors.404', [], 404);
});
```

## Simulación de Métodos HTTP en Formularios

Los formularios HTML solo soportan los métodos GET y POST. Para usar otros métodos, incluye un campo `_method`:

```html
<form method="POST" action="/users/1">
    <input type="hidden" name="_method" value="DELETE">
    <!-- o -->
    @method('DELETE')
</form>
```

## Accediendo a la Ruta Actual

Puedes acceder a información sobre la ruta actual:

```php
$route = request()->route();
$name = $route->getName();
$action = $route->getAction();
$parameters = $route->parameters();
```

## Protección CSRF

LightWeight protege tu aplicación contra ataques de falsificación de solicitudes entre sitios:

```html
<form method="POST" action="/profile">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <!-- o -->
    @csrf
</form>
```

Para excluir rutas de la protección CSRF, agrégalas al array `$except` en `App\Http\Middleware\VerifyCsrfToken`.

## Rutas API

Las rutas API se definen típicamente en `routes/api.php`:

```php
Route::prefix('api/v1')->group(function() {
    Route::get('/users', [ApiController::class, 'getUsers']);
    Route::post('/auth/login', [AuthController::class, 'login']);
});
```

## Caché de Rutas

Para entornos de producción, puedes cachear tus rutas para un mejor rendimiento:

```bash
php light route:cache
```

Para limpiar la caché de rutas:

```bash
php light route:clear
```

## Mejores Prácticas

1. **Mantén las definiciones de rutas limpias**: Usa controladores en lugar de rutas con closures para lógica compleja.
2. **Utiliza rutas de recursos** para operaciones CRUD.
3. **Nombra tus rutas** para una referencia más fácil.
4. **Agrupa rutas relacionadas** para mantener tu código organizado.
5. **Usa middleware con prudencia** para filtrar solicitudes.
6. **Considera la versión para APIs** utilizando prefijos.

## Depuración de Rutas

Para listar todas las rutas registradas:

```bash
php light route:list
```

## Conclusión

El sistema de enrutamiento de LightWeight proporciona una forma flexible y potente de definir los puntos de entrada de tu aplicación. Al utilizar rutas con nombre, grupos de rutas y vinculación de modelos, puedes construir aplicaciones limpias y mantenibles.

Para más información sobre temas relacionados, consulta:
- [Guía de Controladores](controllers-guide.md)
- [Guía de Middleware](middleware-guide.md)
- [Manejo de Peticiones y Respuestas](request-response-handling.md)
