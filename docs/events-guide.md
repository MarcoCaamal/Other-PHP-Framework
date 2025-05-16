# Guía del Sistema de Eventos

El framework LightWeight incluye un sistema de eventos simple pero poderoso basado en el patrón observador. Este sistema permite a tu aplicación reaccionar a diferentes eventos que ocurren durante la ejecución.

## Conceptos Básicos

- **Eventos**: Los eventos son objetos que representan que algo ha ocurrido en la aplicación. Por ejemplo, `ApplicationBootstrapped` se dispara cuando la aplicación termina de inicializarse.
  
- **Listeners**: Los listeners son funciones o clases que responden a eventos específicos. Cuando ocurre un evento, todos los listeners registrados para ese evento son ejecutados.

## Disparar Eventos

Hay dos formas de disparar eventos:

### 1. Usando la función helper `event()`

```php
// Usando un nombre de evento (string)
event('user.registered', ['user' => $user]);

// Usando un objeto de evento
$event = new UserRegistered($user);
event($event);
```

### 2. Usando el dispatcher de eventos directamente

```php
app(EventDispatcherInterface::class)->dispatch('user.registered', ['user' => $user]);

// O usando la instancia de App
app()->events->dispatch('user.registered', ['user' => $user]);
```

## Escuchar Eventos

Hay varias formas de registrar listeners para eventos:

### 1. Usando la función helper `on()`

```php
// Usando una función anónima
on('user.registered', function ($event) {
    $user = $event->getData()['user'];
    // Hacer algo con el usuario...
});
```

### 2. Usando una clase Listener

```php
class SendWelcomeEmailListener implements ListenerInterface
{
    public function handle(EventInterface $event): void
    {
        $user = $event->getData()['user'];
        // Enviar email de bienvenida...
    }
}

// Registrar el listener
on('user.registered', new SendWelcomeEmailListener());
```

### 3. Usando el dispatcher de eventos directamente

```php
app()->events->listen('user.registered', function ($event) {
    // Manejar el evento
});
```

## Crear Eventos Personalizados

Para crear un evento personalizado, puedes extender la clase base `Event`:

```php
namespace App\Events;

use LightWeight\Events\Event;

class UserRegistered extends Event
{
    public function getName(): string
    {
        return 'user.registered';
    }
    
    // Opcionalmente, puedes añadir métodos específicos para acceder a los datos
    public function getUser()
    {
        return $this->data['user'] ?? null;
    }
}
```

## Eventos del Sistema

El framework proporciona algunos eventos del sistema predefinidos:

- `application.bootstrapped`: Se dispara cuando la aplicación ha terminado de inicializarse.
- `application.terminating`: Se dispara justo antes de que la aplicación termine y envíe la respuesta.

Puedes escuchar estos eventos para ejecutar lógica en momentos específicos del ciclo de vida de la aplicación.

## Ejemplos de Uso

### Ejemplo 1: Registro de acciones del usuario

```php
// En tu controlador
public function login()
{
    // Lógica de login...
    
    // Disparar evento
    event('user.logged_in', ['user' => $user, 'time' => time()]);
    
    return redirect('/dashboard');
}

// En tu archivo de configuración o service provider
on('user.logged_in', function ($event) {
    $data = $event->getData();
    Log::info("Usuario {$data['user']->name} ha iniciado sesión en: " . date('Y-m-d H:i:s', $data['time']));
});
```

### Ejemplo 2: Enviar notificaciones cuando ocurre un error

```php
on('application.error', function ($event) {
    $exception = $event->getData()['exception'];
    NotificationService::send("Se ha producido un error: {$exception->getMessage()}");
});
```

El sistema de eventos es una herramienta poderosa para desacoplar diferentes partes de tu aplicación, permitiendo una arquitectura más modular y extensible.

## Olvidar Listeners

Si necesitas eliminar listeners de un evento específico o de todos los eventos, puedes usar la función `forget_listeners()`:

```php
// Eliminar todos los listeners de un evento específico
forget_listeners('user.logged_in');

// Eliminar todos los listeners de todos los eventos
forget_listeners();
```

## Mejores Prácticas

1. **Nombrado de eventos** - Usa nombres descriptivos y sigue una convención como `recurso.acción` (ej: `user.created`, `invoice.paid`).

2. **Eventos vs. Llamadas directas** - Usa eventos cuando:
   - Una acción tiene múltiples efectos secundarios (registrar, enviar emails, actualizar caché)
   - Quieres permitir que los plugins/módulos reaccionen a ciertos sucesos
   - Necesitas desacoplar componentes

3. **Mantén los eventos ligeros** - Incluye solo los datos necesarios para los listeners.

4. **Documentación** - Mantén una lista de los eventos disponibles en tu aplicación y su propósito.

## Depuración

Para depurar problemas con eventos, puedes implementar un listener de depuración:

```php
on('*', function ($event) {
    Log::debug('Evento disparado: ' . $event->getName(), $event->getData());
});
```

> Nota: El wildcard `*` no está implementado por defecto, pero puedes extender el EventDispatcher para soportarlo si fuera necesario.

## Proveedor de Servicios para Eventos

LightWeight incluye un `EventServiceProvider` que facilita la configuración y registro de listeners de forma centralizada. Para más información sobre cómo utilizar este proveedor, consulta la [guía del proveedor de servicios de eventos](event-service-provider.md).
