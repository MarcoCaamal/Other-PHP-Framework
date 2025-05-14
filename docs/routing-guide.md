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

// Redirección temporal (302)
Route::redirect('/temporary', '/current');
```

## Parámetros de Ruta

Las rutas pueden contener parámetros que capturan partes de la URI:

```php
// Parámetro obligatorio
Route::get('/users/{id}', [UserController::class, 'show']);

// Parámetros múltiples
Route::get('/posts/{post}/comments/{comment}', [CommentController::class, 'show']);
```

Estos parámetros se inyectan automáticamente en los métodos del controlador:

```php
public function show($id)
{
    $user = User::find($id);
    return view('users.show', compact('user'));
}
```

### Parámetros Opcionales

Puedes hacer que un parámetro sea opcional añadiendo un signo de interrogación:

```php
Route::get('/users/{id?}', [UserController::class, 'index']);
```

En este caso, debes proporcionar un valor predeterminado en tu controlador:

```php
public function index($id = null)
{
    if ($id) {
        $user = User::find($id);
        return view('users.show', compact('user'));
    }
    
    $users = User::all();
    return view('users.index', compact('users'));
}
```

### Restricciones de Parámetros

Puedes restringir el formato de los parámetros de ruta usando expresiones regulares:

```php
// Solo números
Route::get('/users/{id}', [UserController::class, 'show'])->where('id', '[0-9]+');

// Solo letras
Route::get('/categories/{slug}', [CategoryController::class, 'show'])->where('slug', '[a-zA-Z]+');

// Formato personalizado
Route::get('/posts/{slug}', [PostController::class, 'show'])->where('slug', '[a-z0-9-]+');

// Múltiples restricciones
Route::get('/posts/{post}/comments/{comment}', [CommentController::class, 'show'])
    ->where([
        'post' => '[0-9]+',
        'comment' => '[0-9]+'
    ]);
```

## Nombres de Ruta

Asignar nombres a las rutas permite generar URLs o redirecciones a esas rutas sin tener que especificar manualmente la URL completa:

```php
Route::get('/user/profile', [UserController::class, 'profile'])->setName('profile');
```

Para generar URLs a rutas nombradas:

```php
// En un controlador o vista
$url = route('profile');

// Con parámetros
$url = route('user.show', ['id' => 1]);
```

## Grupos de Rutas

Los grupos de rutas permiten compartir atributos comunes entre múltiples rutas, como prefijos, middlewares o espacios de nombres:

### Prefijos de Rutas

```php
Route::$prefix = '/admin';

Route::get('/dashboard', [AdminController::class, 'dashboard']);
Route::get('/users', [AdminController::class, 'users']);
Route::get('/settings', [AdminController::class, 'settings']);

// Restablecer prefijo
Route::$prefix = '';
```

### Grupos con Middleware

```php
// Aplicar middleware a un grupo de rutas
Route::$prefix = '/admin';
Route::setGroupMiddlewares([
    AuthMiddleware::class,
    AdminMiddleware::class,
]);

Route::get('/dashboard', [AdminController::class, 'dashboard']);
Route::get('/users', [AdminController::class, 'users']);
Route::get('/settings', [AdminController::class, 'settings']);

// Restablecer configuración
Route::$prefix = '';
Route::setGroupMiddlewares([]);
```

### Grupos Anidados

También puedes anidar grupos de rutas:

```php
Route::$prefix = '/admin';
Route::setGroupMiddlewares([AuthMiddleware::class]);

// Rutas generales de administración
Route::get('/dashboard', [AdminController::class, 'dashboard']);

// Subgrupo para usuarios 
$previousPrefix = Route::$prefix;
$previousMiddlewares = Route::getGroupMiddlewares();

Route::$prefix = Route::$prefix . '/users';
Route::setGroupMiddlewares(array_merge(
    $previousMiddlewares, 
    [UserManagementMiddleware::class]
));

Route::get('/', [AdminUserController::class, 'index']);
Route::get('/create', [AdminUserController::class, 'create']);
Route::get('/{id}/edit', [AdminUserController::class, 'edit']);

// Restaurar configuración anterior
Route::$prefix = $previousPrefix;
Route::setGroupMiddlewares($previousMiddlewares);
```

## Rutas de Recurso

Para recursos RESTful, LightWeight proporciona un método conveniente que crea todas las rutas necesarias:

```php
Route::resource('photos', PhotoController::class);
```

Esto genera las siguientes rutas:

| Método HTTP | URI                 | Acción   | Nombre de la ruta |
|-------------|---------------------|----------|-------------------|
| GET         | /photos             | index    | photos.index      |
| GET         | /photos/create      | create   | photos.create     |
| POST        | /photos             | store    | photos.store      |
| GET         | /photos/{id}        | show     | photos.show       |
| GET         | /photos/{id}/edit   | edit     | photos.edit       |
| PUT/PATCH   | /photos/{id}        | update   | photos.update     |
| DELETE      | /photos/{id}        | destroy  | photos.destroy    |

### Rutas de Recurso Parciales

Si solo necesitas un subconjunto de las rutas de recurso:

```php
// Solo rutas específicas
Route::resource('photos', PhotoController::class, [
    'only' => ['index', 'show']
]);

// Excluir rutas específicas
Route::resource('photos', PhotoController::class, [
    'except' => ['create', 'store', 'edit', 'update', 'destroy']
]);
```

### Recursos Anidados

Puedes anidar recursos para representar relaciones:

```php
Route::resource('photos', PhotoController::class);
Route::resource('photos.comments', CommentController::class);
```

Esto generará rutas como `/photos/{photo}/comments/{comment}`.

## Acceso a la Información de Ruta Actual

Dentro de tus controladores y vistas, puedes acceder a la información sobre la ruta actual:

```php
// Verificar la ruta actual por nombre
if (request()->route()->name() === 'users.show') {
    // Lógica específica para esta ruta
}

// Obtener parámetros de la ruta
$userId = request()->routeParameter('id');
```

## Middleware de Ruta

Puedes aplicar middleware a rutas individuales:

```php
Route::get('/profile', [UserController::class, 'profile'])
    ->setMiddlewares([AuthMiddleware::class]);
```

O a grupos de rutas, como vimos anteriormente:

```php
Route::setGroupMiddlewares([AuthMiddleware::class]);
```

## Orígenes de Ruta

### Definición en Archivos

Las rutas se pueden definir en diferentes archivos para mantener tu aplicación organizada:

- `routes/web.php` - Para rutas web accesibles desde el navegador
- `routes/api.php` - Para rutas de API
- `routes/console.php` - Para comandos de consola
- `routes/channels.php` - Para canales de difusión (WebSockets)

### Carga de Archivos de Rutas

En el archivo `bootstrap/app.php` o en un proveedor de servicios, puedes cargar tus archivos de rutas:

```php
$router = new Router();

// Cargar rutas web
require_once __DIR__ . '/../routes/web.php';

// Cargar rutas de API
require_once __DIR__ . '/../routes/api.php';

// Registrar el router en el contenedor
$app->singleton(Router::class, function() use ($router) {
    return $router;
});
```

## Grupo de Rutas API

Para rutas de API, es común aplicar middleware específicos:

```php
Route::$prefix = '/api';
Route::setGroupMiddlewares([
    JsonResponseMiddleware::class,
    ApiAuthMiddleware::class,
]);

Route::get('/users', [ApiController::class, 'getUsers']);
Route::post('/users', [ApiController::class, 'createUser']);
Route::get('/users/{id}', [ApiController::class, 'getUser']);

// Restaurar configuración
Route::$prefix = '';
Route::setGroupMiddlewares([]);
```

## Fallbacks

Puedes definir una ruta de fallback que se ejecutará cuando ninguna otra ruta coincida con la solicitud:

```php
Route::fallback(function() {
    return view('errors.404');
});
```

## Generación de URLs

### Rutas con Nombre

```php
// Generar URL para una ruta con nombre
$url = route('users.show', ['id' => 1]); // /users/1

// Generar URL con parámetros de consulta
$url = route('users.index', ['search' => 'John', 'sort' => 'name']); // /users?search=John&sort=name
```

### URL a Función

```php
// Generar URL para la acción de un controlador
$url = action([UserController::class, 'show'], ['id' => 1]);
```

### URLs Relativas y Absolutas

```php
// URL relativa
$url = url('/users'); // /users

// URL absoluta
$url = url('/users', true); // https://example.com/users
```

## Redirecciones

### Redirección a Rutas Nombradas

```php
return redirect()->route('users.index');
```

### Redirección con Flash Data

```php
return redirect()->route('dashboard')
                 ->with('status', 'Profile updated!');
```

### Redirección con Entrada

```php
return redirect()->back()->withInput();
```

## Inspeccionando Rutas

Para depurar o inspeccionar las rutas registradas:

```php
// En un comando de consola
php light.php route:list
```

También puedes crear un controlador para mostrar todas las rutas:

```php
public function showRoutes()
{
    $router = app(Router::class);
    $routes = [];
    
    foreach ($router->getRoutes() as $method => $routesForMethod) {
        foreach ($routesForMethod as $route) {
            $routes[] = [
                'method' => $method,
                'uri' => $route->uri(),
                'name' => $route->name() ?? 'unnamed',
                'action' => $this->getRouteAction($route->action()),
                'middleware' => array_map(
                    fn($m) => get_class($m), 
                    $route->middlewares()
                ),
            ];
        }
    }
    
    return view('routes', compact('routes'));
}

private function getRouteAction($action)
{
    if ($action instanceof \Closure) {
        return 'Closure';
    }
    
    if (is_array($action)) {
        return is_object($action[0]) 
            ? get_class($action[0]) . '@' . $action[1]
            : $action[0] . '@' . $action[1];
    }
    
    return 'Unknown';
}
```

## Procesamiento de Rutas

Internamente, LightWeight realiza el siguiente proceso cuando llega una solicitud:

1. Determina el método HTTP y la URI de la solicitud.
2. Busca en las rutas registradas para ese método HTTP.
3. Compara la URI de la solicitud con el patrón de cada ruta.
4. Si encuentra una coincidencia, extrae los parámetros de ruta.
5. Ejecuta los middleware asociados a esa ruta.
6. Invoca la acción de la ruta (función anónima o método de controlador).
7. Devuelve la respuesta al cliente.

## Optimización del Desempeño del Enrutador

Para aplicaciones grandes con muchas rutas, puedes mejorar el rendimiento:

```php
// En un proveedor de servicios
public function boot()
{
    if ($this->app->environment('production')) {
        $cachedRoutes = $this->app->storagePath() . '/framework/routes.php';
        
        if (file_exists($cachedRoutes)) {
            $this->app->routeCollection = require $cachedRoutes;
            return;
        }
    }
    
    // Carga normal de rutas si no hay caché
    $this->loadRoutes();
}

// Comando para generar la caché de rutas
public function handle()
{
    $router = $this->app->make(Router::class);
    
    // Cargar todas las rutas
    require_once base_path('routes/web.php');
    require_once base_path('routes/api.php');
    
    // Serializar y guardar las rutas
    file_put_contents(
        $this->app->storagePath() . '/framework/routes.php',
        '<?php return ' . var_export($router->getRoutes(), true) . ';'
    );
    
    $this->info('Routes cached successfully!');
}
```

## Buenas Prácticas

1. **Organización**: Agrupa rutas relacionadas y usa archivos separados para diferentes tipos de rutas.

2. **Nombrado**: Siempre nombra tus rutas con nombres descriptivos y consistentes.

3. **Jerarquía de Recursos**: Usa rutas anidadas para representar relaciones de recursos.

4. **Validación de Parámetros**: Utiliza restricciones para validar parámetros de ruta.

5. **Comentarios**: Agrega comentarios para explicar rutas complejas o no convencionales.

## Ejemplos Prácticos

### Aplicación Web Típica

```php
// Rutas públicas
Route::get('/', [HomeController::class, 'index'])->setName('home');
Route::get('/about', [HomeController::class, 'about'])->setName('about');
Route::get('/contact', [HomeController::class, 'contact'])->setName('contact');
Route::post('/contact', [HomeController::class, 'submitContact'])->setName('contact.submit');

// Rutas de autenticación
Route::get('/login', [AuthController::class, 'loginForm'])->setName('login');
Route::post('/login', [AuthController::class, 'login'])->setName('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->setName('logout');
Route::get('/register', [AuthController::class, 'registerForm'])->setName('register');
Route::post('/register', [AuthController::class, 'register'])->setName('register.submit');

// Rutas protegidas
Route::$prefix = '/dashboard';
Route::setGroupMiddlewares([AuthMiddleware::class]);

Route::get('/', [DashboardController::class, 'index'])->setName('dashboard');
Route::get('/settings', [SettingsController::class, 'index'])->setName('settings');

// Recursos completos
Route::resource('projects', ProjectController::class);

// Recursos anidados
Route::resource('projects.tasks', TaskController::class);

Route::$prefix = '';
Route::setGroupMiddlewares([]);
```

### API RESTful

```php
Route::$prefix = '/api';
Route::setGroupMiddlewares([
    ApiKeyMiddleware::class,
    JsonResponseMiddleware::class,
]);

// API v1
Route::$prefix = Route::$prefix . '/v1';

// Endpoints de usuarios
Route::get('/users', [ApiUserController::class, 'index']);
Route::post('/users', [ApiUserController::class, 'store']);
Route::get('/users/{id}', [ApiUserController::class, 'show']);
Route::put('/users/{id}', [ApiUserController::class, 'update']);
Route::delete('/users/{id}', [ApiUserController::class, 'destroy']);

// Endpoints de autenticación
Route::post('/auth/login', [ApiAuthController::class, 'login']);
Route::post('/auth/register', [ApiAuthController::class, 'register']);
Route::post('/auth/refresh', [ApiAuthController::class, 'refresh']);
Route::post('/auth/logout', [ApiAuthController::class, 'logout'])
    ->setMiddlewares([JwtAuthMiddleware::class]);

// Restablecer configuración
Route::$prefix = '';
Route::setGroupMiddlewares([]);
```

## Conclusión

El sistema de enrutamiento de LightWeight proporciona una forma flexible y potente de definir cómo tu aplicación responde a las solicitudes HTTP. La organización correcta de tus rutas es fundamental para mantener tu aplicación limpia y mantenible a medida que crece. Utiliza las funciones de agrupación, restricción de parámetros y middleware para crear una estructura de rutas robusta que represente adecuadamente los recursos y acciones de tu aplicación.
