# Sistema de Gestión de Request y Response

## Introducción

El sistema de gestión de solicitudes (Request) y respuestas (Response) es una parte fundamental de cualquier framework web moderno. LightWeight proporciona una implementación robusta y flexible que permite manejar fácilmente las peticiones HTTP entrantes y generar las respuestas adecuadas.

## La Clase Request

La clase `Request` representa una solicitud HTTP entrante y proporciona una interfaz limpia para acceder a todos los datos asociados con esa solicitud.

### Creación de una Instancia Request

LightWeight crea automáticamente una instancia de `Request` para cada solicitud HTTP entrante. Esta instancia está disponible en tus controladores a través de la inyección de dependencias:

```php
use LightWeight\Http\Request;

public function store(Request $request)
{
    // Trabajar con la solicitud
}
```

### Acceso a Datos de la Solicitud

#### Datos del Formulario/POST

Para acceder a los datos enviados a través de un formulario o solicitud POST:

```php
// Obtener todos los datos POST
$allData = $request->data();

// Obtener un valor específico
$name = $request->data('name');

// Con valor predeterminado si no existe
$page = $request->data('page') ?? 1;
```

#### Parámetros de Consulta (Query String)

Para acceder a los parámetros de la URL (`?param=value`):

```php
// Todos los parámetros de consulta
$queryParams = $request->query();

// Un parámetro específico
$search = $request->query('search');

// Con valor predeterminado
$sort = $request->query('sort') ?? 'name';
```

#### Parámetros de Ruta

Los parámetros definidos en las rutas (como `/users/{id}`) están disponibles a través de:

```php
// Obtener todos los parámetros de ruta
$routeParams = $request->routeParameters();

// Obtener un parámetro específico
$id = $request->routeParameters('id');
```

#### Encabezados HTTP

Para acceder a los encabezados de la solicitud:

```php
// Todos los encabezados
$headers = $request->headers();

// Un encabezado específico
$contentType = $request->headers('Content-Type');
$userAgent = $request->headers('User-Agent');
```

#### Archivos Subidos

Para trabajar con archivos subidos:

```php
// Comprobar si se ha subido un archivo
if ($request->file('avatar')) {
    // Obtener la instancia del archivo
    $file = $request->file('avatar');
    
    // Comprobar si es una imagen
    if ($file->isImage()) {
        // Es una imagen
    }
    
    // Obtener la extensión
    $extension = $file->extension();
    
    // Almacenar en una ruta predefinida
    $path = $file->store('avatars');
}
```

### Validación de Datos

LightWeight integra un sistema de validación para los datos de entrada:

```php
// Validar datos con reglas
try {
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email',
        'age' => 'integer|min:18|max:100',
    ]);
    
    // Los datos son válidos, continuar procesando
} catch (\LightWeight\Validation\Exceptions\ValidationException $e) {
    // La validación falló
    $errors = $e->errors;
    // Manejar errores
}
```

## La Clase Response

La clase `Response` representa la respuesta HTTP que se enviará al cliente después de procesar la solicitud.

### Creación de Respuestas Básicas

```php
use LightWeight\Http\Response;

// Respuesta con contenido y código de estado
$response = new Response();
$response->setContent('Contenido');
$response->setStatus(200);

// Establecer encabezados
$response->setHeader('Content-Type', 'text/plain');
$response->setHeaders([
    'X-Custom-Header' => 'Valor',
    'X-Another-Header' => 'Otro Valor',
]);

// Eliminar un encabezado
$response->removeHeader('X-Custom-Header');
```

### Tipos de Respuesta Comunes

#### Vistas

Para devolver una vista:

```php
// En un método de controlador
return view('users.profile', ['user' => $user]);

// Con datos compactos
$user = User::find($id);
return view('users.profile', compact('user'));
```

#### JSON

Para devolver datos JSON:

```php
// Conversión automática de arrays y objetos a JSON
return json([
    'name' => 'John',
    'email' => 'john@example.com',
    'roles' => ['admin', 'user'],
]);

// Con código de estado personalizado
return json(['error' => 'No encontrado'], 404);
```

#### Texto Plano

```php
return Response::text('Contenido de texto plano');
```

#### Redirecciones

```php
// Redirección simple
return redirect('/dashboard');

// Redirección atrás (a la página anterior)
return back();
```

## Gestión Avanzada de Solicitudes y Respuestas

### Middleware de Solicitud/Respuesta

Los middlewares pueden modificar la solicitud antes de que llegue al controlador, o modificar la respuesta antes de que se envíe al cliente:

```php
<?php

namespace App\Middleware;

use LightWeight\Http\Contracts\MiddlewareContract;
use LightWeight\Http\Contracts\RequestContract;
use LightWeight\Http\Response;

class CorsMiddleware implements MiddlewareContract
{
    public function handle(RequestContract $request, \Closure $next)
    {
        // Si es una solicitud OPTIONS (preflight), devolver respuesta inmediata
        if ($request->method()->value === 'OPTIONS') {
            $response = new Response();
            $response->setStatus(200);
        } else {
            // Procesar la solicitud normalmente
            $response = $next($request);
        }
        
        // Modificar la respuesta para agregar encabezados CORS
        $response->setHeader('Access-Control-Allow-Origin', '*');
        $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        
        return $response;
    }
}
```

### Interceptar la Solicitud

Para interceptar y manipular la solicitud antes de que alcance un controlador:

```php
<?php

namespace App\Middleware;

use LightWeight\Http\Contracts\MiddlewareContract;
use LightWeight\Http\Contracts\RequestContract;

class TransformInputMiddleware implements MiddlewareContract
{
    public function handle(RequestContract $request, \Closure $next)
    {
        // Transformar datos de entrada (por ejemplo, recortar espacios en blanco)
        $input = $request->data();
        
        foreach ($input as $key => $value) {
            if (is_string($value)) {
                $input[$key] = trim($value);
            }
        }
        
        // Remplazar los datos de entrada con los datos transformados
        $request->setPostData($input);
        
        return $next($request);
    }
}
```

## Mejores Prácticas

1. **Validación Temprana**: Valida los datos de entrada al comienzo de los métodos del controlador.

2. **Respuestas Consistentes**: Mantén un formato coherente para todas tus respuestas, especialmente en APIs.

3. **Códigos de Estado Apropiados**: Utiliza los códigos de estado HTTP adecuados para las respuestas.

4. **Seguridad**: Sanitiza siempre los datos de entrada para evitar problemas de seguridad.

5. **Manejo de Errores**: Utiliza try-catch para manejar excepciones y devolver respuestas de error adecuadas.

## Ejemplos Avanzados

### API RESTful

```php
<?php

namespace App\Controllers\Api;

use LightWeight\Http\ControllerBase;
use LightWeight\Http\Request;
use LightWeight\Http\Response;
use App\Models\Product;

class ProductController extends ControllerBase
{
    public function __construct()
    {
        $this->setMiddlewares([
            \App\Middleware\ApiAuthMiddleware::class,
        ]);
    }
    
    public function index(Request $request): Response
    {
        $products = DB::table('products')->select('*');
        
        return json([
            'data' => $products,
        ]);
    }
    
    public function store(Request $request): Response
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'description' => 'nullable|string',
            ]);
            
            DB::table('products')->insert($validated);
            
            return json([
                'message' => 'Producto creado correctamente'
            ])->setStatus(201);
            
        } catch (\Throwable $e) {
            return json([
                'message' => 'Error al crear el producto',
                'errors' => $e->getMessage(),
            ])->setStatus(422);
        }
    }
    
    // Otros métodos para show, update, destroy...
}
```

### Manejo de Formulario con Carga de Archivos

```php
<?php

namespace App\Controllers;

use LightWeight\Http\ControllerBase;
use LightWeight\Http\Request;
use LightWeight\Http\Response;

class ProfileController extends ControllerBase
{
    public function update(Request $request, $id): Response
    {
        try {
            // Validar entrada
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email',
                'bio' => 'nullable|string|max:1000',
            ]);
            
            // Manejar subida de avatar
            $avatarPath = null;
            if ($request->file('avatar')) {
                // Almacenar nuevo avatar
                $avatarPath = $request->file('avatar')->store('avatars');
            }
            
            // Actualizar usuario en la base de datos
            DB::table('users')
                ->where('id', $id)
                ->update([
                    ...$validated,
                    'avatar_path' => $avatarPath ?? DB::raw('avatar_path')
                ]);
            
            // Redireccionar con mensaje de éxito
            return redirect('/profile');
            
        } catch (\Exception $e) {
            return redirect('/profile/edit')->withErrors([
                'error' => 'Error al actualizar perfil'
            ]);
        }
    }
}
```
