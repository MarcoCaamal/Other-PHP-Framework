# Proveedor de Servicios de Eventos

> 🌐 [English Documentation](../en/event-service-provider.md)

El framework LightWeight ahora incluye un proveedor de servicios dedicado para el sistema de eventos. Este proveedor simplifica la configuración y el registro de listeners globales para tu aplicación.

## EventServiceProvider

El `EventServiceProvider` es responsable de:
- Registrar la implementación del `EventDispatcherInterface` en el contenedor
- Facilitar el registro de listeners predeterminados
- Cargar automáticamente suscriptores desde la configuración

## Configuración

El archivo de configuración `config/events.php` permite configurar aspectos del sistema de eventos:

```php
return [
    /**
     * Event subscribers
     * 
     * List of subscriber classes that will be automatically registered with the event dispatcher.
     * Each subscriber class must have a subscribe method that accepts an EventDispatcherInterface
     * instance as its only parameter.
     */
    'subscribers' => [
        App\Events\Subscribers\UserEventSubscriber::class,
    ],
    
    /**
     * Event logging
     * 
     * When enabled, all events will be logged for debugging purposes.
     */
    'log_events' => env('LOG_EVENTS', false),
    
    /**
     * Events that should not be logged even when event logging is enabled
     */
    'log_exclude' => [
        'application.bootstrapped',
    ],
];
```

## Creando tu propio Event Service Provider

Puedes extender el `EventServiceProvider` para registrar listeners específicos para tu aplicación:

```php
<?php

namespace App\Providers;

use App\Events\Listeners\SendWelcomeEmail;
use LightWeight\Events\Contracts\EventDispatcherInterface;
use LightWeight\Providers\EventServiceProvider as BaseEventServiceProvider;

class AppEventServiceProvider extends BaseEventServiceProvider
{
    /**
     * Lista de listeners a registrar
     */
    protected array $listen = [
        'user.registered' => [
            SendWelcomeEmail::class,
        ],
        'application.bootstrapped' => [
            function ($event) {
                // Realizar tareas cuando la aplicación termina de inicializarse
            }
        ]
    ];
    
    /**
     * Register additional event-related services
     */
    public function registerServices($container)
    {
        // Llamar al método padre para registrar el EventDispatcher
        parent::registerServices($container);
        
        // Añadir servicios adicionales relacionados con eventos si es necesario
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

use LightWeight\Events\Contracts\EventDispatcherInterface;
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
    public function subscribe(EventDispatcherInterface $dispatcher): void
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
