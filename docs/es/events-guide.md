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
app()->events()->dispatch('user.registered', ['user' => $user]);
```

## Registrar Listeners de Eventos

Hay varias formas de registrar listeners de eventos:

### 1. Usando la función helper `on()`

```php
// Usando una clausura (closure)
on('user.registered', function($event) {
    $user = $event->getData()['user'];
    // Enviar email de bienvenida
    mailTemplate($user->email, 'Bienvenido', 'welcome', ['userName' => $user->name]);
});

// Usando un método de clase
on('user.registered', [UserNotifier::class, 'sendWelcomeEmail']);
```

### 2. Usando el dispatcher de eventos directamente

```php
app(EventDispatcherInterface::class)->addListener('user.registered', function($event) {
    // Manejar el evento
});
```

### 3. Usando Suscriptores de Eventos

Un suscriptor de eventos es una clase que puede registrar múltiples listeners para diferentes eventos:

```php
class UserEventSubscriber implements SubscriberInterface
{
    public function getEvents(): array
    {
        return [
            'user.registered' => 'onUserRegistered',
            'user.login' => 'onUserLogin',
            'user.logout' => 'onUserLogout'
        ];
    }
    
    public function onUserRegistered($event)
    {
        // Manejar evento de registro de usuario
    }
    
    public function onUserLogin($event)
    {
        // Manejar evento de inicio de sesión
    }
    
    public function onUserLogout($event)
    {
        // Manejar evento de cierre de sesión
    }
}

// Registrar el suscriptor
app(EventDispatcherInterface::class)->addSubscriber(new UserEventSubscriber());
```

## Provider de Servicios de Eventos

Para una organización más limpia, puedes registrar todos tus listeners de eventos en un `EventServiceProvider`:

```php
<?php

namespace App\Providers;

use LightWeight\Events\Contracts\SubscriberInterface;
use LightWeight\Events\ServiceProvider;
use App\Events\Subscribers\UserEventSubscriber;
use App\Events\Subscribers\OrderEventSubscriber;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Registrar listeners y suscriptores de eventos
     */
    public function register(): void
    {
        // Registrar listeners individuales de eventos
        $this->events->addListener('app.bootstrapped', function($event) {
            // Hacer algo cuando la aplicación se haya inicializado
        });
        
        // Registrar suscriptores de eventos
        $this->registerSubscribers([
            UserEventSubscriber::class,
            OrderEventSubscriber::class,
        ]);
    }
    
    /**
     * Registrar un array de suscriptores
     */
    protected function registerSubscribers(array $subscribers): void
    {
        foreach ($subscribers as $subscriber) {
            $this->events->addSubscriber(app($subscriber));
        }
    }
}
```

Luego registra el provider de servicios en el proceso de inicialización de tu aplicación:

```php
$app->register(App\Providers\EventServiceProvider::class);
```

Para más detalles sobre los providers de servicios, consulta la [documentación del Event Service Provider](event-service-provider.md).

> **Nota Importante**: Si experimentas el error `PHP Fatal error: Constant expression contains invalid operations` al intentar usar closures en tus providers de eventos, consulta la guía específica sobre [Error de Expresión Constante](constant-expression-error.md) para entender el problema y su solución.

## Eventos del Sistema

LightWeight dispara automáticamente varios eventos del sistema:

- `app.bootstrapped`: Disparado cuando la aplicación termina de inicializarse
- `app.shutdown`: Disparado cuando la aplicación está a punto de cerrarse
- `router.matched`: Disparado cuando se ha encontrado una ruta coincidente
- `view.rendering`: Disparado antes de renderizar una vista
- `view.rendered`: Disparado después de que una vista ha sido renderizada
- `session.started`: Disparado cuando se inicia una sesión
- `auth.login`: Disparado cuando un usuario inicia sesión
- `auth.logout`: Disparado cuando un usuario cierra sesión
- `auth.attempt`: Disparado cuando se realiza un intento de inicio de sesión
- `model.creating`: Disparado antes de crear un modelo
- `model.created`: Disparado después de crear un modelo
- `model.updating`: Disparado antes de actualizar un modelo
- `model.updated`: Disparado después de actualizar un modelo
- `model.deleting`: Disparado antes de eliminar un modelo
- `model.deleted`: Disparado después de eliminar un modelo

## Crear Eventos Personalizados

Puedes crear clases de eventos personalizados:

```php
<?php

namespace App\Events;

use LightWeight\Events\Event;
use App\Models\Order;

class OrderShipped extends Event
{
    protected Order $order;
    
    public function __construct(Order $order)
    {
        $this->order = $order;
    }
    
    public function getOrder(): Order
    {
        return $this->order;
    }
}
```

Luego dispara el evento:

```php
$order = Order::find($id);
event(new OrderShipped($order));
```

Y registra un listener:

```php
on(OrderShipped::class, function(OrderShipped $event) {
    $order = $event->getOrder();
    // Enviar notificación, actualizar inventario, etc.
});
```

## Mejores Prácticas

1. **Usa objetos de eventos**: Para eventos complejos, crea clases de eventos dedicadas en lugar de usar nombres de strings.
2. **Usa suscriptores para eventos relacionados**: Agrupa listeners de eventos relacionados en clases suscriptoras.
3. **Mantén los listeners enfocados**: Cada listener debe tener una única responsabilidad.
4. **No confíes en el orden de ejecución**: No asumas que los listeners se ejecutarán en un orden específico.
5. **Ten cuidado con el rendimiento**: Si tienes muchos listeners de eventos, ten en cuenta el impacto en el rendimiento.
6. **Usa eventos para desacoplar**: Los eventos son excelentes para desacoplar componentes, pero no los uses en exceso.
