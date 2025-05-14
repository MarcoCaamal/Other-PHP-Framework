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
        
        return redirect('/users');
    }
    
    // Otros métodos: show, edit, update, destroy...
}
```

## Generación de Controladores

Puedes generar un controlador rápidamente usando el comando CLI:

```bash
php light.php make:controller UserController
```

Para crear un controlador con métodos CRUD predefinidos (Create, Read, Update, Delete):

```bash
php light.php make:controller ProductController --resource
```

> Nota: Verifica que estos comandos estén implementados en tu versión específica del framework.

## Métodos del Controlador

### Convención de Nombres

LightWeight sigue una convención para los nombres de los métodos en controladores de recursos:

| Método HTTP | URI            | Método del Controlador | Descripción                         |
|-------------|----------------|------------------------|-------------------------------------|
| GET         | /users         | index()                | Mostrar lista de recursos           |
| GET         | /users/create  | create()               | Mostrar formulario de creación      |
| POST        | /users         | store()                | Almacenar un nuevo recurso          |
| GET         | /users/{id}    | show()                 | Mostrar un recurso específico       |
| GET         | /users/{id}/edit | edit()               | Mostrar formulario de edición       |
| PUT/PATCH   | /users/{id}    | update()               | Actualizar un recurso específico    |
| DELETE      | /users/{id}    | destroy()              | Eliminar un recurso específico      |

### Inyección de Dependencias

LightWeight permite la inyección automática de dependencias en los métodos del controlador:

```php
public function show(Request $request, $id, UserService $userService): Response
{
    $user = $userService->findById($id);
    return view('users.show', compact('user'));
}
```

## Middleware en Controladores

Los middlewares permiten ejecutar código antes o después de que una solicitud sea procesada por un controlador.

### Definiendo Middlewares en Controladores

```php
class DashboardController extends ControllerBase
{
    public function __construct()
    {
        $this->setMiddlewares([
            AuthMiddleware::class,
            AdminCheckMiddleware::class
        ]);
    }
    
    public function index(): Response
    {
        return view('dashboard.index');
    }
}
```

### Aplicando Middlewares a Métodos Específicos

Para aplicar middleware sólo a ciertos métodos:

```php
class ArticleController extends ControllerBase
{
    public function __construct()
    {
        // Asignar middlewares a todas las rutas manejadas por este controlador
        $this->setMiddlewares([LogRequestMiddleware::class]);
    }
}
```

## Reutilización de Código

### Controlador Base Personalizado

Puedes crear un controlador base personalizado para compartir lógica común:

```php
<?php

namespace App\Controllers;

use LightWeight\Http\ControllerBase;

class AppController extends ControllerBase
{
    protected $user;
    
    public function __construct()
    {
        $this->user = Auth::user();
        
        // Middleware común para toda la aplicación
        $this->setMiddlewares([WebMiddleware::class]);
    }
    
    protected function checkPermission($action): void
    {
        if (!$this->user || !$this->userHasPermission($action)) {
            Response::text('No autorizado')->setStatus(403);
            exit;
        }
    }
    
    protected function userHasPermission($action): bool
    {
        // Implementa tu lógica de verificación de permisos
        return true; // Ejemplo simple
    }
}
```

### Traits para Funcionalidad Compartida

Los traits son una excelente manera de compartir funcionalidad entre controladores:

```php
<?php

namespace App\Traits;

use LightWeight\Http\Response;

trait ApiResponder
{
    protected function respondSuccess($data, string $message = '', int $code = 200): Response
    {
        return json([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ])->setStatus($code);
    }
    
    protected function respondError(string $message, int $code = 400): Response
    {
        return json([
            'success' => false,
            'message' => $message,
        ])->setStatus($code);
    }
}

// Uso en un controlador
class ApiUserController extends ControllerBase
{
    use ApiResponder;
    
    public function index(): Response
    {
        $users = User::all();
        return $this->respondSuccess($users, 'Usuarios recuperados');
    }
}
```

## Respuestas del Controlador

Los controladores pueden devolver diferentes tipos de respuestas:

```php
// Vistas
return view('users.index', ['users' => $users]);

// JSON
return json(['name' => 'John', 'email' => 'john@example.com']);

// Texto plano
return Response::text('Contenido simple');

// Redirecciones
return redirect('/dashboard');
return back();

// Respuestas con código de estado
return Response::text('No autorizado')->setStatus(403);

// Métodos adicionales que podrías implementar:
// - Descargas de archivos
// - Streaming de contenido
```

## Validación en Controladores

LightWeight proporciona un sistema de validación para los datos de entrada:

```php
public function store(Request $request): Response
{
    $validated = $request->validate([
        'title' => 'required|min:3|max:255',
        'body' => 'required',
        'category_id' => 'required|numeric',
        'published' => 'boolean',
    ]);
    
    $article = Article::create($validated);
    
    return redirect('/articles/' . $article->id);
}
```

## Buenas Prácticas

1. **Controladores delgados, modelos gordos**: Mantén la lógica de negocio en los modelos o servicios, no en los controladores.

2. **Un controlador por recurso**: Sigue el principio de responsabilidad única.

3. **Manejo de errores adecuado**: Utiliza try/catch para manejar excepciones y devolver respuestas apropiadas.

4. **Validación temprana**: Valida los datos de entrada al principio del método.

5. **Documentación clara**: Documenta cada método del controlador con PHPDoc.

## Ejemplo Avanzado

```php
<?php

namespace App\Controllers;

use App\Models\Product;
use App\Services\InventoryService;
use LightWeight\Http\ControllerBase;
use LightWeight\Http\Request;
use LightWeight\Http\Response;
use Throwable;

class ProductController extends ControllerBase
{
    protected InventoryService $inventoryService;
    
    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
        
        $this->setMiddlewares([
            AuthMiddleware::class,
        ]);
        
        $this->setMethodMiddlewares('store', [
            ValidateProductMiddleware::class
        ]);
    }
    
    public function index(Request $request): Response
    {
        $category = $request->query('category');
        $sort = $request->query('sort', 'name');
        $direction = $request->query('direction', 'asc');
        
        // Aquí implementarías tu propia lógica de filtrado y ordenamiento
        $products = Product::all();
        if ($category) {
            $products = Product::where('category_id', $category)->get();
        }
        
        return view('products.index', compact('products', 'category', 'sort', 'direction'));
    }
    
    public function store(Request $request): Response
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'description' => 'nullable|string',
                'category_id' => 'required|exists:categories,id',
                'image' => 'nullable|image|max:2048',
            ]);
            
            // Manejo de archivos
            if ($request->file('image')) {
                $file = $request->file('image');
                // Implementa tu lógica para guardar el archivo
                $validated['image_path'] = '/path/to/saved/file.jpg';
            }
            
            // Crear el producto
            $product = Product::create($validated);
            $this->inventoryService->initializeInventory($product, $request->data('stock', 0));
            
            return redirect('/products/' . $product->id);
                
        } catch (Throwable $e) {
            // Manejo de errores
            error_log('Error al crear producto: ' . $e->getMessage());
            
            return back()->withErrors([
                'error' => 'Error al crear el producto: ' . $e->getMessage()
            ]);
        }
    }
    
    // Otros métodos CRUD...
}
```
