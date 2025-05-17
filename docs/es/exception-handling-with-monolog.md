# Manejo de Excepciones con Monolog

LightWeight proporciona un sistema robusto de manejo de excepciones con registro integrado utilizando Monolog. Este documento explica cómo funciona el sistema de manejo de excepciones y cómo puedes personalizarlo para tu aplicación.

## Descripción General

El manejo de excepciones en LightWeight se gestiona a través de varios componentes:

1. **ExceptionHandler** - Manejador base para capturar y procesar excepciones
2. **Integración con Monolog** - Proporciona registro estructurado para excepciones
3. **Sistema de Notificaciones** - Para excepciones críticas que requieren atención inmediata

## Uso Básico

El framework captura y procesa automáticamente todas las excepciones no manejadas. Dependiendo del entorno y la configuración, realizará:

1. Registro de la excepción con el contexto apropiado
2. Mostrar páginas de error detalladas en desarrollo o páginas de error genéricas en producción
3. Enviar notificaciones para excepciones críticas

## Configuración

El manejo de excepciones se puede configurar en `config/exceptions.php`:

```php
return [
    // Si se debe mostrar información detallada de errores
    'debug' => env('APP_DEBUG', false),
    
    // Configuración de registro
    'log' => [
        'channel' => 'daily',
        'max_files' => 30,
        'daily' => true,
        'level' => 'error',
        'path' => 'logs/exceptions.log',
        'critical_path' => 'logs/critical.log',
    ],
    
    // Configuración de notificaciones
    'notifications' => [
        'channels' => ['log', 'email'],
        'email' => [
            'to' => 'admin@example.com',
        ],
        // Otras configuraciones de canales de notificación...
    ],
    
    // Plantillas de vista
    'views' => [
        'not_found' => 'errors.404',
        'validation' => 'errors.validation',
        'database' => 'errors.database',
        'general' => 'errors.application',
    ],
];
```

## Manejador de Excepciones Personalizado

Puedes crear un manejador de excepciones personalizado extendiendo el manejador base:

```php
<?php

namespace App\Exceptions;

use LightWeight\Exceptions\ExceptionHandler as BaseHandler;
use Throwable;

class Handler extends BaseHandler
{
    // Excepciones que no deberían reportarse
    protected array $dontReport = [
        // Agrega clases de excepción aquí
    ];
    
    // Registrar manejadores de excepciones personalizados
    public function register(): void
    {
        // Ejemplo: Manejar excepciones de API
        $this->registerHandler(ApiException::class, function ($e, $request) {
            return Response::json([
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ])->setStatus(400);
        });
    }
}
```

## Registro de Excepciones

El framework registra automáticamente las excepciones utilizando Monolog. El registro incluye:

- Tipo y mensaje de la excepción
- Archivo y línea donde ocurrió la excepción
- Traza de la pila
- Información de contexto

Puedes acceder al logger directamente si es necesario:

```php
try {
    // Algún código que podría lanzar excepciones
} catch (Throwable $e) {
    logMessage($e->getMessage(), [
        'exception' => get_class($e),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'code' => $e->getCode(),
        'trace' => $e->getTraceAsString(),
    ], 'error');
}
```

## Notificaciones de Excepciones Críticas

Para excepciones que requieren atención inmediata, puedes:

1. Crear una excepción personalizada que extienda `CriticalException`
2. Configurar canales de notificación en la configuración de excepciones
3. El framework enviará automáticamente notificaciones cuando ocurran estas excepciones

Ejemplo:

```php
class DatabaseConnectionException extends CriticalException
{
    // Especificar qué canales usar para notificaciones
    public function getNotificationChannels(): array
    {
        return ['log', 'email', 'slack'];
    }
}
```

## Configuración Avanzada

### Canales de Registro Personalizados

Puedes agregar canales de registro personalizados creando un proveedor de servicios que extienda el sistema de registro de excepciones:

```php
class AppExceptionServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $logger = $this->app->get('exception.logger');
        
        // Agregar un manejador personalizado para excepciones de base de datos
        $dbFormatter = new LineFormatter(...);
        $dbHandler = new StreamHandler(...);
        $dbHandler->setFormatter($dbFormatter);
        
        $logger->pushHandler($dbHandler);
    }
}
```

### Renderizadores de Excepciones Personalizados

Puedes personalizar cómo se renderizan las excepciones implementando métodos de renderizado personalizados en tu manejador de excepciones:

```php
protected function renderCustomException(CustomException $e): ResponseContract
{
    return Response::view('errors.custom', [
        'exception' => $e
    ])->setStatus(500);
}
```

## Temas Avanzados

### Filtrado de Informes de Excepciones

Puedes controlar qué excepciones se reportan sobrescribiendo el método `shouldReport`:

```php
public function shouldReport(Throwable $e): bool
{
    // No reportar excepciones de validación
    if ($e instanceof ValidationException) {
        return false;
    }
    
    return parent::shouldReport($e);
}
```

### Canales de Notificación Personalizados

Para implementar canales de notificación personalizados, extiende el manejador de excepciones e implementa un método de notificación personalizado:

```php
protected function sendNotification(string $channel, array $context, Throwable $e): void
{
    if ($channel === 'custom-channel') {
        // Lógica de notificación personalizada
        return;
    }
    
    parent::sendNotification($channel, $context, $e);
}
```
