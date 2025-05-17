# Proveedor de Servicios de Eventos

> 🌐 [English Documentation](../en/event-service-provider.md)

El framework LightWeight incluye un proveedor de servicios dedicado para el sistema de eventos. Este proveedor simplifica la configuración y el registro de listeners globales para tu aplicación, y también gestiona el registro de eventos si está habilitado.

## EventServiceProvider

El `EventServiceProvider` es responsable de:
- Registrar la implementación del `EventDispatcherContract` en el contenedor
- Facilitar el registro de listeners predeterminados
- Cargar automáticamente suscriptores desde la configuración
- Configurar el registro de eventos según los ajustes de tu aplicación

## Configuración

### Configuración de Eventos

El archivo de configuración `config/events.php` permite configurar aspectos del sistema de eventos:

```php
return [
    /**
     * Event subscribers
     * 
     * Lista de clases suscriptoras que se registrarán automáticamente con el despachador de eventos.
     * Cada clase suscriptora debe tener un método subscribe que acepte una instancia de EventDispatcherContract
     * como su único parámetro.
     */
    'subscribers' => [
        App\Events\Subscribers\UserEventSubscriber::class,
    ],
];
```

### Configuración de Registro de Eventos

El registro de eventos se configura en el archivo `config/logging.php`:

```php
return [
    // ... otras configuraciones de logging
    
    /**
     * Configuración de Logging de Eventos
     *
     * Ajustes para el registro automático de eventos despachados en la aplicación.
     */
    'event_logging' => [
        /**
         * Habilitar el logging de eventos.
         */
        'enabled' => env('LOG_EVENTS', false),
        
        /**
         * Eventos que no deben ser registrados incluso cuando el logging de eventos está habilitado.
         */
        'excluded_events' => [
            'application.bootstrapped',
            'router.matched',
            // Otros eventos a excluir...
        ],
    ],
];
```

## Creando tu propio Event Service Provider

Puedes extender el `EventServiceProvider` para registrar listeners específicos para tu aplicación:

```php
<?php

namespace App\Providers;

use App\Events\Listeners\SendWelcomeEmailListener;
use LightWeight\Events\Contracts\EventDispatcherContract;
use LightWeight\Providers\EventServiceProvider as BaseEventServiceProvider;

class AppEventServiceProvider extends BaseEventServiceProvider
{
    /**
     * Lista de listeners a registrar
     * 
     * IMPORTANTE: Solo se permiten referencias a clases aquí.
     * Para closures, usa el método registerServices.
     * 
     * @var array<string, array<class-string>>
     */
    protected array $listen = [
        'user.registered' => [
            SendWelcomeEmailListener::class,
        ],
    ];
    
    /**
     * Registra servicios de aplicación y listeners de eventos
     *
     * @param \DI\Container $container El contenedor de inyección de dependencias
     * @return void
     */
    public function registerServices($container)
    {
        // Llamar al método padre para registrar el EventDispatcher y los listeners basados en clases
        parent::registerServices($container);
        
        // Obtiene el dispatcher de eventos para registrar listeners basados en closures
        $dispatcher = $container->get(EventDispatcherContract::class);
        
        // Registra listeners basados en closures
        $dispatcher->listen('user.login', function ($event) {
            // Lógica para manejar el inicio de sesión
            $user = $event->getData()['user'] ?? null;
            if ($user) {
                // Ejemplo: Actualizar fecha de último login
                // $user->updateLastLogin();
            }
        });
        
        $dispatcher->listen('application.bootstrapped', function ($event) {
            // Lógica para ejecutar cuando la aplicación ha sido inicializada
        });
    }
}
```

Luego, registra tu proveedor en `config/providers.php`:

```php
return [
    'boot' => [
        // ... otros proveedores
        App\Providers\AppEventServiceProvider::class,
    ],
    // ...
];
```

## Suscriptores de Eventos

Los suscriptores de eventos son clases que permiten agrupar múltiples listeners relacionados en un solo lugar. Para crear un suscriptor:

```php
<?php

namespace App\Events\Subscribers;

use LightWeight\Events\Contracts\EventDispatcherContract;
use LightWeight\Events\Contracts\EventInterface;
use LightWeight\Events\Contracts\EventSubscriberInterface;

class UserEventSubscriber implements EventSubscriberInterface
{
    /**
     * Obtiene los eventos manejados por este suscriptor
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'user.registered' => 'onUserRegistered',
            'user.login' => 'onUserLogin'
        ];
    }
    
    /**
     * Registrar los listeners para el suscriptor
     */
    public function subscribe(EventDispatcherContract $dispatcher): void
    {
        $dispatcher->listen('user.registered', function (EventInterface $event) {
            $this->onUserRegistered($event);
        });
        
        $dispatcher->listen('user.login', function (EventInterface $event) {
            $this->onUserLogin($event);
        });
    }
    
    /**
     * Manejar el evento de registro de usuario
     */
    public function onUserRegistered(EventInterface $event): void
    {
        // Implementación...
    }
    
    /**
     * Manejar el evento de inicio de sesión
     */
    public function onUserLogin(EventInterface $event): void
    {
        // Implementación...
    }
}
```

Los suscriptores proporcionan una forma organizada de gestionar listeners relacionados.

## Nota Importante Sobre Closures y Errores de Expresión Constante

En PHP, cuando se definen propiedades de clase con valores iniciales, esos valores deben ser expresiones constantes. Las funciones anónimas (closures) no se consideran expresiones constantes, por lo que no se pueden usar directamente en la definición de la propiedad `$listen`. Intentar hacerlo resultará en un error fatal de PHP.

### Ejemplo del error

```php
// Esto causará un error: PHP Fatal error: Constant expression contains invalid operations
protected array $listen = [
    'user.login' => [
        function ($event) { /* ... */ },  // No permitido como valor de propiedad
    ],
];
```

### Solución correcta

En lugar de intentar definir closures en la propiedad `$listen`, debes registrarlos directamente mediante el método `registerServices` usando el dispatcher de eventos:

```php
public function registerServices($container)
{
    // Primero llamar al padre para manejar los listeners de clase
    parent::registerServices($container);
    
    // Luego registrar los closures
    $dispatcher = $container->get(EventDispatcherContract::class);
    
    $dispatcher->listen('user.login', function ($event) {
        // Lógica para el evento...
    });
}
```

Este enfoque evita la limitación de PHP relacionada con expresiones constantes mientras mantiene la capacidad de usar closures como listeners de eventos.

Para una guía más detallada sobre cuándo usar closures vs clases de listeners, consulta [Mejores Prácticas para Listeners de Eventos](event-listener-best-practices.md).
