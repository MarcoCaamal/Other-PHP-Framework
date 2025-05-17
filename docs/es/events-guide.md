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

LightWeight implementa los siguientes eventos del sistema:

- `app.bootstrapped`: Disparado cuando la aplicación termina de inicializarse
- `application.terminating`: Disparado cuando la aplicación está a punto de cerrarse
- `router.matched`: Disparado cuando se encuentra una ruta coincidente con la solicitud actual
- `view.rendering`: Disparado antes de que se renderice una vista
- `view.rendered`: Disparado después de que una vista ha sido renderizada

## Ejemplos de Uso de Eventos del Sistema

### Evento `router.matched`

Puedes utilizar este evento para realizar acciones cuando una ruta específica es accedida:

```php
on('router.matched', function ($event) {
    $route = $event->getRoute();
    $uri = $event->getUri();
    $method = $event->getMethod();
    
    // Registrar acceso a una ruta específica
    if ($uri === '/admin/dashboard') {
        app('log')->info("Acceso al panel de administración detectado. Método: {$method}");
    }
    
    // También puedes hacer comprobaciones de seguridad adicionales
    // o cualquier otra operación que necesites cuando se acceda a ciertas rutas
});
```

### Evento `app.bootstrapped`

Este evento es útil para ejecutar código después de que la aplicación ha terminado de inicializarse:

```php
on('app.bootstrapped', function ($event) {
    // Inicializar servicios que deben estar disponibles durante toda la vida de la aplicación
    app('cache')->warmUp();
    
    // O configurar valores globales
    app('settings')->load();
});
```

### Evento `application.terminating`

Puedes usar este evento para realizar limpieza o acciones finales antes de que la aplicación termine:

```php
on('application.terminating', function ($event) {
    // Obtener la respuesta que se enviará
    $response = $event->getData()['response'];
    
    // Registrar el tiempo de respuesta
    $startTime = app('timer')->getStartTime();
    $endTime = microtime(true);
    app('log')->info("Tiempo de respuesta: " . ($endTime - $startTime) . " segundos");
    
    // Guardar estadísticas o hacer limpieza final
    app('stats')->save();
});
```

### Evento `view.rendering`

Puedes utilizar este evento para modificar parámetros de vista o realizar acciones antes de que una vista sea renderizada:

```php
on('view.rendering', function ($event) {
    $view = $event->getView();
    $params = $event->getParams();
    $layout = $event->getLayout();
    
    // Añadir datos globales a todas las vistas
    if (!isset($params['user']) && auth()->check()) {
        $params['user'] = auth()->user();
        
        // Puedes modificar parámetros accediendo a ellos a través del array $event->getData()
        $event->getData()['params'] = $params;
    }
    
    // Registrar la renderización de vistas para depuración
    app('log')->debug("Renderizando vista: {$view}");
    
    // Realizar acciones personalizadas para vistas específicas
    if ($view === 'admin/dashboard') {
        // Registrar acceso de administrador o realizar comprobaciones de seguridad
    }
});
```

### Evento `view.rendered`

Este evento es útil para el post-procesamiento del contenido renderizado o para registrar el rendimiento de las vistas:

```php
on('view.rendered', function ($event) {
    $view = $event->getView();
    $content = $event->getContent();
    
    // Medir y registrar el tiempo de renderizado para vistas específicas
    if (str_starts_with($view, 'informes/')) {
        app('log')->info("Vista de informe {$view} renderizada en " . (microtime(true) - FRAMEWORK_START_TIME) . " segundos");
    }
    
    // También podrías realizar manipulación de contenido después del renderizado si es necesario
    // Nota: En este punto, el contenido ya ha sido enviado al buffer de salida
    // por lo que las modificaciones no afectarán a la respuesta actual
    
    // Sin embargo, puedes capturar métricas o analizar el contenido renderizado
    if (config('app.debug') && strlen($content) > 1000000) {
        app('log')->warning("Vista grande renderizada: {$view} - Tamaño: " . strlen($content) . " bytes");
    }
});
```

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
