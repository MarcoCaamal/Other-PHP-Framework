# Controladores en LightWeight

## Introducción

Los controladores son una parte fundamental del patrón MVC (Modelo-Vista-Controlador) implementado en LightWeight Framework. Actúan como intermediarios entre los modelos y las vistas, procesando las solicitudes HTTP, interactuando con los datos y devolviendo las respuestas adecuadas.

## Estructura Básica

En LightWeight, todos los controladores extienden de la clase base `ControllerBase`. Un controlador típico tiene la siguiente estructura:

```php
<?php

namespace App\Controllers;

use LightWeight\Http\ControllerBase;
use LightWeight\Http\Request;
use LightWeight\Http\Response;

class UserController extends ControllerBase
{
    /**
     * Mostrar una lista de usuarios
     */
    public function index(Request $request): Response
    {
        $users = User::all();
        
        return view('users.index', compact('users'));
    }
    
    /**
     * Mostrar el formulario para crear un nuevo usuario
     */
    public function create(): Response
    {
        return view('users.create');
    }
    
    /**
     * Almacenar un nuevo usuario
     */
    public function store(Request $request): Response
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);
        
        User::create($validated);
        
        return redirect('/users')->withSuccess('Usuario creado con éxito');
    }
    
    /**
     * Mostrar el usuario especificado
     */
    public function show(Request $request, $id): Response
    {
        $user = User::find($id);
        
        if (!$user) {
            return response()->notFound('Usuario no encontrado');
        }
        
        return view('users.show', compact('user'));
    }
    
    /**
     * Mostrar el formulario para editar un usuario
     */
    public function edit(Request $request, $id): Response
    {
        $user = User::find($id);
        
        if (!$user) {
            return response()->notFound('Usuario no encontrado');
        }
        
        return view('users.edit', compact('user'));
    }
    
    /**
     * Actualizar el usuario especificado
     */
    public function update(Request $request, $id): Response
    {
        $user = User::find($id);
        
        if (!$user) {
            return response()->notFound('Usuario no encontrado');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
        ]);
        
        $user->update($validated);
        
        return redirect('/users/'.$id)->withSuccess('Usuario actualizado con éxito');
    }
    
    /**
     * Eliminar el usuario especificado
     */
    public function destroy(Request $request, $id): Response
    {
        $user = User::find($id);
        
        if (!$user) {
            return response()->notFound('Usuario no encontrado');
        }
        
        $user->delete();
        
        return redirect('/users')->withSuccess('Usuario eliminado con éxito');
    }
}
```

## Creación de Controladores

Puedes crear un controlador utilizando la herramienta de línea de comandos:

```bash
php light make:controller UserController
```

Para crear un controlador con métodos de recursos (index, create, store, show, edit, update, destroy):

```bash
php light make:controller UserController --resource
```

## Inyección de Dependencias

LightWeight soporta la inyección automática de dependencias en los métodos del controlador:

```php
public function index(Request $request, UserService $userService): Response
{
    $users = $userService->getAllUsers();
    
    return view('users.index', compact('users'));
}
```

El framework resolverá e inyectará automáticamente la instancia de `UserService` cuando se llame al método `index`.

## Middleware en Controladores

Puedes aplicar middleware a los controladores de varias formas:

### En el Constructor

```php
class UserController extends ControllerBase
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('log')->only(['store', 'update', 'destroy']);
        $this->middleware('subscribed')->except(['index', 'show']);
    }
}
```

### En la Definición de Ruta

```php
Route::get('/users', [UserController::class, 'index'])->middleware('auth');
```

## Controladores de Acción Única

Para casos simples, puedes crear un controlador con un solo método `__invoke`:

```php
<?php

namespace App\Controllers;

use LightWeight\Http\ControllerBase;
use LightWeight\Http\Request;
use LightWeight\Http\Response;

class ShowDashboardController extends ControllerBase
{
    public function __invoke(Request $request): Response
    {
        $stats = $this->getDashboardStats();
        
        return view('dashboard', compact('stats'));
    }
    
    protected function getDashboardStats(): array
    {
        // Obtener estadísticas del dashboard
        return [
            'users' => User::count(),
            'posts' => Post::count(),
            'comments' => Comment::count(),
        ];
    }
}
```

Definición de ruta para un controlador de acción única:

```php
Route::get('/dashboard', ShowDashboardController::class);
```

## Controladores API

Los controladores de API típicamente devuelven respuestas JSON en lugar de vistas HTML:

```php
<?php

namespace App\Controllers\Api;

use LightWeight\Http\ControllerBase;
use LightWeight\Http\Request;
use LightWeight\Http\Response;

class UserApiController extends ControllerBase
{
    public function index(): Response
    {
        $users = User::all();
        
        return Response::json($users);
    }
    
    public function show($id): Response
    {
        $user = User::find($id);
        
        if (!$user) {
            return Response::json(['error' => 'Usuario no encontrado'], 404);
        }
        
        return Response::json($user);
    }
    
    public function store(Request $request): Response
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);
        
        $user = User::create($validated);
        
        return Response::json($user, 201);
    }
    
    // Métodos adicionales para update, destroy, etc.
}
```

## Validación de Solicitudes

Los controladores a menudo necesitan validar los datos de las solicitudes entrantes. LightWeight proporciona una forma conveniente de hacerlo:

```php
public function store(Request $request): Response
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'content' => 'required',
        'published_at' => 'nullable|date',
        'category_id' => 'required|exists:categories,id',
    ]);
    
    $post = Post::create($validated);
    
    return redirect()->route('posts.show', $post->id);
}
```

Si la validación falla, la solicitud redirigirá automáticamente hacia atrás con errores. En un contexto de API, devolverá una respuesta JSON con los errores de validación.

## Devolviendo Respuestas

Los controladores pueden devolver varios tipos de respuestas:

### Respuestas de Vista

```php
return view('users.index', ['users' => $users]);
```

### Respuestas JSON

```php
return Response::json(['name' => 'John', 'email' => 'john@example.com']);
```

### Respuestas de Redirección

```php
return redirect('/dashboard');
return redirect()->route('users.show', ['id' => $user->id]);
return redirect()->back();
return redirect()->with('status', '¡Perfil actualizado!');
```

### Descargas de Archivos

```php
return Response::download('/path/to/file.pdf', 'informe.pdf');
```

### Respuestas Personalizadas

```php
return Response::make('Contenido personalizado', 200, ['Content-Type' => 'text/plain']);
```

## Controladores de Recursos

Los controladores de recursos proporcionan una forma conveniente de organizar operaciones CRUD con nombres convencionales:

```php
// Rutas para un controlador de recursos
Route::resource('posts', PostController::class);
```

Esta única línea crea las siguientes rutas:

| Método HTTP | URI                | Acción  | Nombre de Ruta |
|-------------|-------------------|---------|----------------|
| GET         | /posts            | index   | posts.index    |
| GET         | /posts/create     | create  | posts.create   |
| POST        | /posts            | store   | posts.store    |
| GET         | /posts/{id}       | show    | posts.show     |
| GET         | /posts/{id}/edit  | edit    | posts.edit     |
| PUT/PATCH   | /posts/{id}       | update  | posts.update   |
| DELETE      | /posts/{id}       | destroy | posts.destroy  |

Puedes limitar los métodos incluidos en la ruta de recursos:

```php
Route::resource('posts', PostController::class, ['only' => ['index', 'show']]);
Route::resource('posts', PostController::class, ['except' => ['create', 'store', 'edit', 'update', 'destroy']]);
```

## Organización de Controladores

Para aplicaciones más grandes, organizar los controladores en subdirectorios puede ayudar a mantener una estructura limpia:

```
app/
├── Controllers/
│   ├── Api/
│   │   ├── AuthController.php
│   │   ├── UserController.php
│   │   └── PostController.php
│   ├── Admin/
│   │   ├── DashboardController.php
│   │   ├── UserManagementController.php
│   │   └── SettingsController.php
│   ├── Auth/
│   │   ├── LoginController.php
│   │   ├── RegisterController.php
│   │   └── ForgotPasswordController.php
│   ├── HomeController.php
│   ├── UserController.php
│   └── PostController.php
```

## Mejores Prácticas

1. **Mantén los controladores enfocados**: Cada controlador debe manejar un aspecto específico de tu aplicación.
2. **Usa controladores de recursos** para operaciones CRUD estándar.
3. **Valida los datos de entrada**: Siempre valida los datos de las solicitudes entrantes para mantener la integridad y seguridad de los datos.
4. **Evita la lógica de negocio en los controladores**: Los controladores deben ser delgados y principalmente coordinar la interacción entre modelos y vistas. Mueve la lógica de negocio compleja a clases de servicio.
5. **Usa inyección de dependencias** para los servicios que necesita el controlador.
6. **Nomenclatura consistente**: Sigue convenciones como nombres de recursos pluralizados (UsersController) y nombres de acciones significativos.
7. **Usa indicaciones de tipo** para mejor legibilidad del código y detección de errores.
8. **Maneja los errores con elegancia**: Proporciona respuestas apropiadas para los casos de error.

## Temas Relacionados

- [Guía de Enrutamiento](routing-guide.md)
- [Manejo de Solicitudes y Respuestas](request-response-handling.md)
- [Guía de Middleware](middleware-guide.md)
- [Guía de Validación](validation-guide.md)
