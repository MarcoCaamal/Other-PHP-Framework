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
php light make:middleware AuthMiddleware
```

Esto creará un nuevo archivo de middleware en `app/Middleware/AuthMiddleware.php`.

## Registrando Middleware

Para que un middleware sea utilizado, debe registrarse. LightWeight soporta dos tipos de middleware:

1. **Middleware global**: Se aplica a todas las solicitudes HTTP
2. **Middleware de ruta**: Se aplica solo a rutas específicas

### Registrando Middleware Global

El middleware global se registra en el archivo `bootstrap/app.php`:

```php
// bootstrap/app.php

// ...

// Registrar middleware global
$app->middleware([
    \App\Middleware\TrimStrings::class,
    \App\Middleware\ConvertEmptyStringsToNull::class,
]);

// ...
```

### Registrando Middleware de Ruta

El middleware de ruta se registra con un nombre en el archivo `bootstrap/app.php` y luego se aplica a rutas específicas:

```php
// bootstrap/app.php

// ...

// Registrar middleware de ruta
$app->routeMiddleware([
    'auth' => \App\Middleware\AuthMiddleware::class,
    'cache' => \App\Middleware\CacheResponseMiddleware::class,
    'throttle' => \App\Middleware\ThrottleRequestsMiddleware::class,
]);

// ...
```

Luego, puedes aplicar el middleware a rutas específicas:

```php
// routes/web.php

use LightWeight\Routing\Route;

Route::get('/profile', 'ProfileController@show')->middleware('auth');

// Aplicar múltiples middleware
Route::get('/api/users', 'Api\UserController@index')->middleware(['auth', 'throttle']);
```

## Parámetros de Middleware

A veces, tu middleware puede necesitar parámetros adicionales. Por ejemplo, un middleware de limitación de solicitudes podría necesitar saber cuántas solicitudes permitir por minuto. Puedes pasar parámetros al middleware añadiendo dos puntos después del nombre del middleware, seguido de los parámetros:

```php
// routes/web.php

Route::get('/api/users', 'Api\UserController@index')->middleware('throttle:60,1');
```

Los parámetros se pasarán al método `handle()` del middleware:

```php
<?php

namespace App\Middleware;

use LightWeight\Http\Contracts\MiddlewareContract;
use LightWeight\Http\Contracts\RequestContract;

class ThrottleRequestsMiddleware implements MiddlewareContract
{
    /**
     * Procesa la solicitud entrante.
     *
     * @param RequestContract $request La solicitud HTTP
     * @param \Closure $next El middleware o controlador siguiente en la cadena
     * @param int $maxAttempts Número máximo de intentos
     * @param int $decayMinutes Ventana de tiempo en minutos
     * @return mixed
     */
    public function handle(RequestContract $request, \Closure $next, int $maxAttempts = 60, int $decayMinutes = 1)
    {
        // Lógica de limitación usando $maxAttempts y $decayMinutes
        
        return $next($request);
    }
}
```

## Grupos de Middleware

Por conveniencia, es posible que desees agrupar middleware relacionados bajo una sola clave. Esto te permite asignar múltiples middleware a una ruta con una sola referencia:

```php
// bootstrap/app.php

// ...

// Registrar grupos de middleware
$app->middlewareGroups([
    'web' => [
        \App\Middleware\EncryptCookies::class,
        \App\Middleware\StartSession::class,
        \App\Middleware\VerifyCsrfToken::class,
    ],
    'api' => [
        \App\Middleware\ThrottleRequestsMiddleware::class.':60,1',
        \App\Middleware\ForceJsonResponse::class,
    ],
]);

// ...
```

Luego, puedes aplicar el grupo de middleware a una ruta:

```php
// routes/web.php

Route::get('/dashboard', 'DashboardController@index')->middleware('web');
```

## Middleware Antes y Después

Como hemos visto en la estructura del middleware, el middleware puede ejecutar código antes y después de que la solicitud sea manejada por la aplicación. Esto permite un control potente sobre el ciclo de vida de la solicitud y la respuesta.

### Middleware Antes

Un middleware "antes" realiza su tarea antes de que la solicitud sea manejada por la aplicación:

```php
<?php

namespace App\Middleware;

use LightWeight\Http\Contracts\MiddlewareContract;
use LightWeight\Http\Contracts\RequestContract;

class BeforeMiddleware implements MiddlewareContract
{
    public function handle(RequestContract $request, \Closure $next)
    {
        // Realizar tarea antes de que se maneje la solicitud
        
        return $next($request);
    }
}
```

### Middleware Después

Un middleware "después" realiza su tarea después de que la solicitud ha sido manejada:

```php
<?php

namespace App\Middleware;

use LightWeight\Http\Contracts\MiddlewareContract;
use LightWeight\Http\Contracts\RequestContract;

class AfterMiddleware implements MiddlewareContract
{
    public function handle(RequestContract $request, \Closure $next)
    {
        $response = $next($request);
        
        // Realizar tarea después de que se ha manejado la solicitud
        
        return $response;
    }
}
```

## Middleware Terminable

Algunos middleware pueden necesitar realizar tareas después de que la respuesta HTTP ha sido enviada al navegador. Para este propósito, LightWeight proporciona un `TerminableMiddlewareContract` que puedes implementar:

```php
<?php

namespace App\Middleware;

use LightWeight\Http\Contracts\MiddlewareContract;
use LightWeight\Http\Contracts\RequestContract;
use LightWeight\Http\Contracts\ResponseContract;
use LightWeight\Http\Contracts\TerminableMiddlewareContract;

class TerminableMiddleware implements MiddlewareContract, TerminableMiddlewareContract
{
    public function handle(RequestContract $request, \Closure $next)
    {
        return $next($request);
    }
    
    public function terminate(RequestContract $request, ResponseContract $response)
    {
        // Realizar tarea después de que la respuesta ha sido enviada al navegador
    }
}
```

## Casos de Uso Comunes

### Middleware de Autenticación

```php
<?php

namespace App\Middleware;

use LightWeight\Auth\Auth;
use LightWeight\Http\Contracts\MiddlewareContract;
use LightWeight\Http\Contracts\RequestContract;

class AuthMiddleware implements MiddlewareContract
{
    public function handle(RequestContract $request, \Closure $next)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }
        
        return $next($request);
    }
}
```

### Middleware CORS

```php
<?php

namespace App\Middleware;

use LightWeight\Http\Contracts\MiddlewareContract;
use LightWeight\Http\Contracts\RequestContract;

class CorsMiddleware implements MiddlewareContract
{
    public function handle(RequestContract $request, \Closure $next)
    {
        $response = $next($request);
        
        $response->header('Access-Control-Allow-Origin', '*');
        $response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        
        return $response;
    }
}
```

### Middleware de Registro de Logs

```php
<?php

namespace App\Middleware;

use LightWeight\Http\Contracts\MiddlewareContract;
use LightWeight\Http\Contracts\RequestContract;
use LightWeight\Log\LogFacade as Log;

class LoggingMiddleware implements MiddlewareContract
{
    public function handle(RequestContract $request, \Closure $next)
    {
        Log::info('Solicitud entrante', [
            'method' => $request->getMethod(),
            'uri' => $request->getUri(),
            'ip' => $request->getClientIp(),
        ]);
        
        $response = $next($request);
        
        Log::info('Respuesta saliente', [
            'status' => $response->getStatusCode(),
        ]);
        
        return $response;
    }
}
```

## Orden de los Middleware

El orden en que se aplica el middleware es importante. Por ejemplo, un middleware de sesión debería ejecutarse antes que un middleware de autenticación que utiliza la sesión. El orden está determinado por el orden en que se lista el middleware en la pila de middleware de tu aplicación.

## Middleware en Controladores

También puedes aplicar middleware directamente en tus controladores:

```php
<?php

namespace App\Controllers;

use LightWeight\Http\ControllerBase;

class UserController extends ControllerBase
{
    public function __construct()
    {
        // Aplicar middleware a todos los métodos en este controlador
        $this->middleware('auth');
        
        // Aplicar middleware solo a métodos específicos
        $this->middleware('throttle:60,1')->only(['store', 'update']);
        
        // Aplicar middleware a todos los métodos excepto los especificados
        $this->middleware('cache')->except(['store', 'update', 'destroy']);
    }
    
    // Métodos del controlador...
}
```

## Conclusión

El middleware es una característica poderosa de LightWeight que te permite filtrar las solicitudes HTTP que ingresan a tu aplicación y modificar las respuestas HTTP que salen de tu aplicación. Al comprender cómo crear, registrar y usar middleware, puedes añadir robustas características de seguridad, registro y otras a tu aplicación.

## Temas Relacionados

- [Guía de Enrutamiento](routing-guide.md)
- [Guía de Controladores](controllers-guide.md)
- [Guía de Autenticación](authentication-guide.md)
