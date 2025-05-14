# Middleware en LightWeight

## Introducción

El middleware proporciona un mecanismo conveniente para filtrar las solicitudes HTTP que ingresan a tu aplicación. Por ejemplo, LightWeight incluye un middleware que verifica si el usuario de tu aplicación está autenticado. Si el usuario no está autenticado, el middleware lo redirigirá a la pantalla de inicio de sesión. Sin embargo, si el usuario está autenticado, el middleware permitirá que la solicitud avance más profundamente en la aplicación.

Los middleware pueden realizar una variedad de tareas además de la autenticación. Un middleware puede registrar todas las solicitudes a tu aplicación, aplicar encabezados CORS, o comprimir las respuestas HTTP. Cada middleware funciona como una capa intermedia que la solicitud HTTP debe atravesar antes de llegar a su destino final.

## Estructura de un Middleware

En LightWeight, un middleware es una clase que implementa la interfaz `MiddlewareContract`. Esta interfaz requiere un único método `handle()` que recibe dos parámetros:

- La solicitud HTTP actual (`RequestContract`)
- Una función de cierre (`Closure`) que pasa la solicitud al siguiente middleware o al controlador

Veamos un ejemplo básico de un middleware:

```php
<?php

namespace App\Middleware;

use LightWeight\Http\Contracts\MiddlewareContract;
use LightWeight\Http\Contracts\RequestContract;

class ExampleMiddleware implements MiddlewareContract
{
    /**
     * Procesa la solicitud entrante.
     *
     * @param RequestContract $request La solicitud HTTP
     * @param \Closure $next El middleware o controlador siguiente en la cadena
     * @return mixed
     */
    public function handle(RequestContract $request, \Closure $next)
    {
        // Código a ejecutar antes de que la solicitud llegue al controlador
        
        // Pasa la solicitud al siguiente middleware o al controlador
        $response = $next($request);
        
        // Código a ejecutar después de que el controlador haya procesado la solicitud
        
        return $response;
    }
}
```

## Creando un Middleware

Para crear un middleware, puedes usar el comando CLI de LightWeight:

```bash
php light.php make:middleware AuthMiddleware
```

Esto generará una nueva clase de middleware en el directorio `app/Middleware` con la estructura básica necesaria.

## Middleware de Autenticación

Veamos un ejemplo de un middleware de autenticación:

```php
<?php

namespace App\Middleware;

use LightWeight\Http\Contracts\MiddlewareContract;
use LightWeight\Http\Contracts\RequestContract;

class AuthMiddleware implements MiddlewareContract
{
    public function handle(RequestContract $request, \Closure $next)
    {
        // Verificar si el usuario está autenticado
        if (!auth()->check()) {
            // Usuario no autenticado, redirigir al inicio de sesión
            return redirect('/login')->with('error', 'Por favor, inicia sesión para continuar');
        }
        
        // Usuario autenticado, permitir que la solicitud continúe
        return $next($request);
    }
}
```

## Middleware de Verificación de Rol

Otro ejemplo común es verificar si un usuario tiene un rol específico:

```php
<?php

namespace App\Middleware;

use LightWeight\Http\Contracts\MiddlewareContract;
use LightWeight\Http\Contracts\RequestContract;

class AdminMiddleware implements MiddlewareContract
{
    public function handle(RequestContract $request, \Closure $next)
    {
        if (!auth()->check() || !auth()->user()->hasRole('admin')) {
            abort(403, 'Acceso no autorizado');
        }
        
        return $next($request);
    }
}
```

## Middleware que Modifica la Respuesta

Los middleware también pueden modificar la respuesta después de que el controlador la ha generado:

```php
<?php

namespace App\Middleware;

use LightWeight\Http\Contracts\MiddlewareContract;
use LightWeight\Http\Contracts\RequestContract;

class AddHeadersMiddleware implements MiddlewareContract
{
    public function handle(RequestContract $request, \Closure $next)
    {
        // Obtener la respuesta del controlador
        $response = $next($request);
        
        // Añadir encabezados personalizados a la respuesta
        $response->setHeader('X-Powered-By', 'LightWeight Framework');
        $response->setHeader('X-Frame-Options', 'DENY');
        
        return $response;
    }
}
```

## Middleware de Registro de Solicitudes

Un middleware puede registrar las solicitudes entrantes y el tiempo que tardan en procesarse:

```php
<?php

namespace App\Middleware;

use LightWeight\Http\Contracts\MiddlewareContract;
use LightWeight\Http\Contracts\RequestContract;

class RequestLogMiddleware implements MiddlewareContract
{
    public function handle(RequestContract $request, \Closure $next)
    {
        // Registrar tiempo de inicio
        $startTime = microtime(true);
        
        // Procesar solicitud
        $response = $next($request);
        
        // Calcular tiempo de procesamiento
        $duration = microtime(true) - $startTime;
        
        // Registrar información
        $logData = [
            'method' => $request->method()->value,
            'uri' => $request->uri(),
            'ip' => $request->ip(),
            'status' => $response->getStatus(),
            'duration' => round($duration * 1000, 2) . 'ms',
        ];
        
        // Guardar en archivo de registro o enviar a un servicio de monitoreo
        file_put_contents(
            storage_path('logs/requests.log'),
            json_encode($logData) . "\n",
            FILE_APPEND
        );
        
        return $response;
    }
}
```

## Registrando Middleware

### Middleware Global

Para aplicar un middleware a todas las solicitudes HTTP, regístralo en el proveedor de servicios en el archivo `app/Providers/RouteServiceProvider.php`:

```php
protected function registerMiddlewares()
{
    $this->router->setGlobalMiddlewares([
        \App\Middleware\CorsMiddleware::class,
        \App\Middleware\RequestLogMiddleware::class,
        \App\Middleware\SecurityHeadersMiddleware::class,
    ]);
}
```

### Middleware de Ruta

Para aplicar middleware a rutas específicas:

```php
// Aplicar a una sola ruta
Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])
    ->setMiddlewares([
        \App\Middleware\AuthMiddleware::class,
        \App\Middleware\AdminMiddleware::class
    ]);

// Aplicar a un grupo de rutas
Route::$prefix = '/admin';
Route::setGroupMiddlewares([
    \App\Middleware\AuthMiddleware::class,
    \App\Middleware\AdminMiddleware::class
]);

Route::get('/dashboard', [AdminController::class, 'dashboard']);
Route::get('/users', [AdminController::class, 'users']);

Route::$prefix = ''; // Resetear prefijo
Route::setGroupMiddlewares([]); // Resetear middlewares de grupo
```

### Middleware en Controladores

Para aplicar middleware a controladores completos o métodos específicos:

```php
<?php

namespace App\Controllers;

use LightWeight\Http\ControllerBase;

class UserController extends ControllerBase
{
    public function __construct()
    {
        // Aplicar a todos los métodos del controlador
        $this->setMiddlewares([
            \App\Middleware\AuthMiddleware::class
        ]);
        
        // Aplicar solo a métodos específicos
        $this->setMethodMiddlewares('store', [
            \App\Middleware\ThrottleMiddleware::class
        ]);
        
        $this->setMethodMiddlewares('update', [
            \App\Middleware\VerifyUserOwnershipMiddleware::class
        ]);
    }
    
    // Métodos del controlador...
}
```

## Grupos de Middleware

Para una mejor organización, puedes definir grupos de middleware en `app/Providers/RouteServiceProvider.php`:

```php
protected function registerMiddlewareGroups()
{
    $this->router->setMiddlewareGroups([
        'web' => [
            \App\Middleware\CsrfMiddleware::class,
            \App\Middleware\SessionMiddleware::class,
        ],
        'api' => [
            \App\Middleware\JsonResponseMiddleware::class,
            \App\Middleware\ThrottleRequestsMiddleware::class,
        ],
        'auth' => [
            \App\Middleware\AuthMiddleware::class,
        ]
    ]);
}
```

Luego puedes aplicar estos grupos a tus rutas:

```php
Route::get('/profile', [ProfileController::class, 'index'])
    ->setMiddlewareGroups(['web', 'auth']);

Route::$prefix = '/api';
Route::setGroupMiddlewareGroups(['api']);

Route::get('/users', [ApiController::class, 'users']);
Route::get('/products', [ApiController::class, 'products']);
```

## Parámetros en Middleware

A veces necesitarás pasar parámetros adicionales a tu middleware:

```php
<?php

namespace App\Middleware;

use LightWeight\Http\Contracts\MiddlewareContract;
use LightWeight\Http\Contracts\RequestContract;

class RoleMiddleware implements MiddlewareContract
{
    protected $roleName;
    
    public function __construct($roleName = 'user')
    {
        $this->roleName = $roleName;
    }
    
    public function handle(RequestContract $request, \Closure $next)
    {
        if (!auth()->check() || !auth()->user()->hasRole($this->roleName)) {
            abort(403, 'Acceso no autorizado');
        }
        
        return $next($request);
    }
}
```

Para usar este middleware con parámetros:

```php
// En un proveedor de servicios o al registrar rutas
$router->setMiddlewareAliases([
    'role' => \App\Middleware\RoleMiddleware::class,
]);

// En la definición de ruta
Route::get('/admin/settings', [AdminController::class, 'settings'])
    ->setMiddlewares([
        auth::class, 
        new \App\Middleware\RoleMiddleware('admin')
    ]);
```

## Orden de Ejecución de Middleware

Los middleware se ejecutan en el orden en que están registrados. El orden de ejecución es:

1. Middleware global (aplicado a todas las solicitudes)
2. Middleware de grupo (aplicado a un grupo de rutas)
3. Middleware de ruta (aplicado a una ruta específica)
4. Middleware de controlador (aplicado a todos los métodos de un controlador)
5. Middleware de método (aplicado a un método específico de un controlador)

Es importante considerar este orden al diseñar y aplicar middleware, ya que los middleware anteriores en la cadena pueden afectar a los posteriores.

## Terminación Temprana

Un middleware puede decidir terminar la cadena de solicitudes y no pasar al siguiente middleware o al controlador:

```php
public function handle(RequestContract $request, \Closure $next)
{
    // Verificar alguna condición
    if ($request->query('api_key') !== config('api.key')) {
        // Terminar la cadena y devolver una respuesta
        return json([
            'error' => 'API Key inválida'
        ])->setStatus(401);
    }
    
    // Si todo está bien, continuar con la cadena
    return $next($request);
}
```

## Patrones Comunes de Middleware

### Compresión de Respuesta

```php
public function handle(RequestContract $request, \Closure $next)
{
    $response = $next($request);
    
    // Comprimir contenido si el cliente lo acepta
    if (strpos($request->header('Accept-Encoding'), 'gzip') !== false) {
        $compressed = gzencode($response->content(), 9);
        
        $response->setContent($compressed)
            ->setHeader('Content-Encoding', 'gzip')
            ->setHeader('Vary', 'Accept-Encoding')
            ->setHeader('Content-Length', strlen($compressed));
    }
    
    return $response;
}
```

### Caché de Respuesta

```php
public function handle(RequestContract $request, \Closure $next)
{
    // Generar clave de caché basada en la URL
    $cacheKey = 'page_cache:' . md5($request->uri());
    
    // Verificar si existe en caché
    if (Cache::has($cacheKey) && !$request->query('nocache')) {
        return Response::text(Cache::get($cacheKey));
    }
    
    // Obtener respuesta normal
    $response = $next($request);
    
    // Guardar en caché si es una respuesta exitosa
    if ($response->getStatus() === 200) {
        Cache::put($cacheKey, $response->content(), 60 * 15); // 15 minutos
    }
    
    return $response;
}
```

### Rastreo y Monitoreo

```php
public function handle(RequestContract $request, \Closure $next)
{
    // Generar ID de rastreo
    $traceId = uniqid('trace-', true);
    
    // Añadir a la solicitud para uso en la aplicación
    $request->setTraceId($traceId);
    
    // Iniciar temporizador
    $startTime = microtime(true);
    
    // Ejecutar solicitud
    $response = $next($request);
    
    // Calcular duración
    $duration = microtime(true) - $startTime;
    
    // Añadir encabezados de rastreo a la respuesta
    $response->setHeader('X-Trace-Id', $traceId);
    $response->setHeader('X-Response-Time', round($duration * 1000, 2));
    
    return $response;
}
```

## Buenas Prácticas

1. **Middleware Ligero**: Mantén tus middleware lo más ligeros posible.

2. **Responsabilidad Única**: Cada middleware debe tener una única responsabilidad.

3. **Considera el Orden**: El orden de ejecución importa; coloca los middleware críticos primero.

4. **Middleware vs. Filtros de Controlador**: Usa middleware para lógica que afecta múltiples rutas y filtros de controlador para lógica específica de controlador.

5. **Manejo de Errores**: Implementa try-catch en middleware críticos para evitar interrupciones en la cadena.

6. **Documenta tus Middleware**: Proporciona comentarios claros sobre qué hace cada middleware y cómo se debe usar.

7. **Evita Estado**: Los middleware deben ser stateless siempre que sea posible.

8. **Optimiza Verificaciones**: Coloca verificaciones rápidas antes que las lentas.

## Ejemplos de Middleware Comunes

### CSRF Protection

```php
<?php

namespace App\Middleware;

use LightWeight\Http\Contracts\MiddlewareContract;
use LightWeight\Http\Contracts\RequestContract;

class CsrfMiddleware implements MiddlewareContract
{
    public function handle(RequestContract $request, \Closure $next)
    {
        $method = $request->method()->value;
        
        // Solo verificar para métodos que modifican datos
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $token = $request->data('_token') ?? $request->header('X-CSRF-Token');
            
            // Verificar token
            if (!$token || !hash_equals(session('_token'), $token)) {
                abort(419, 'Token CSRF inválido');
            }
        }
        
        $response = $next($request);
        
        return $response;
    }
}
```

### Rate Limiting

```php
<?php

namespace App\Middleware;

use LightWeight\Http\Contracts\MiddlewareContract;
use LightWeight\Http\Contracts\RequestContract;

class ThrottleRequestsMiddleware implements MiddlewareContract
{
    protected $maxAttempts;
    protected $decayMinutes;
    
    public function __construct($maxAttempts = 60, $decayMinutes = 1)
    {
        $this->maxAttempts = $maxAttempts;
        $this->decayMinutes = $decayMinutes;
    }
    
    public function handle(RequestContract $request, \Closure $next)
    {
        $key = $this->resolveRequestSignature($request);
        
        if ($this->limiter()->tooManyAttempts($key, $this->maxAttempts)) {
            return $this->buildResponse($key);
        }
        
        $this->limiter()->hit($key, $this->decayMinutes * 60);
        
        $response = $next($request);
        
        return $this->addHeaders(
            $response, 
            $this->maxAttempts,
            $this->limiter()->retriesLeft($key, $this->maxAttempts)
        );
    }
    
    protected function resolveRequestSignature($request)
    {
        return sha1($request->ip() . '|' . $request->uri());
    }
    
    protected function limiter()
    {
        return app('rate.limiter');
    }
    
    protected function buildResponse($key)
    {
        $retryAfter = $this->limiter()->availableIn($key);
        
        return json([
            'error' => 'Demasiadas solicitudes',
            'retryAfter' => ceil($retryAfter / 60) . ' minutos'
        ])->setStatus(429)
          ->setHeader('Retry-After', $retryAfter);
    }
    
    protected function addHeaders($response, $maxAttempts, $remainingAttempts)
    {
        return $response->setHeader('X-RateLimit-Limit', $maxAttempts)
                        ->setHeader('X-RateLimit-Remaining', $remainingAttempts);
    }
}
```

### Middleware de Localización

```php
<?php

namespace App\Middleware;

use LightWeight\Http\Contracts\MiddlewareContract;
use LightWeight\Http\Contracts\RequestContract;

class LocaleMiddleware implements MiddlewareContract
{
    public function handle(RequestContract $request, \Closure $next)
    {
        // Determinar idioma desde diferentes fuentes
        $locale = $request->query('lang') ?? 
                  $request->cookie('locale') ?? 
                  session('locale') ??
                  $this->getLocaleFromHeader($request) ??
                  config('app.locale');
        
        // Validar que el idioma es soportado
        if (!in_array($locale, config('app.supported_locales', ['es']))) {
            $locale = config('app.locale');
        }
        
        // Establecer idioma para esta solicitud
        app()->setLocale($locale);
        
        // Guardar en sesión
        session(['locale' => $locale]);
        
        $response = $next($request);
        
        // Establecer cookie para solicitudes futuras
        $response->setCookie('locale', $locale, 60 * 24 * 30); // 30 días
        
        return $response;
    }
    
    protected function getLocaleFromHeader($request)
    {
        $acceptLanguage = $request->header('Accept-Language');
        
        if (!$acceptLanguage) {
            return null;
        }
        
        // Ejemplo: es-ES,es;q=0.9,en;q=0.8
        $locales = [];
        
        preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $acceptLanguage, $matches);
        
        if (count($matches[1])) {
            foreach ($matches[1] as $index => $locale) {
                $locales[$locale] = $index === 0 ? 1.0 : 0.8;
            }
            
            arsort($locales);
            return array_key_first($locales);
        }
        
        return null;
    }
}
```

## Conclusión

Los middleware son una parte esencial de LightWeight que proporcionan una capa flexible para manejar solicitudes HTTP. Con ellos, puedes implementar funcionalidad reutilizable como autenticación, registro, compresión y muchas otras características sin repetir código en tus controladores.

Aprovecha el poder de los middleware para mantener tu código limpio, modular y bien organizado. Recuerda que cada middleware debe tener una responsabilidad clara y específica, siguiendo el principio de responsabilidad única.
