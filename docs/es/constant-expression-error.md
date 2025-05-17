# Error de Expresión Constante en AppEventServiceProvider

> 🌐 [English Documentation](../en/constant-expression-error.md)

## Descripción del Problema

En versiones anteriores del framework LightWeight, era posible encontrar un error fatal en PHP al intentar utilizar funciones anónimas (closures) dentro de la definición de la propiedad `$listen` en el `AppEventServiceProvider.php`:

```php
// Código que generaba el error
protected array $listen = [
    'user.registered' => [
        \App\Events\Listeners\SendWelcomeEmailListener::class,
    ],
    'user.login' => [
        function ($event) {
            // Lógica para manejar el inicio de sesión
            $user = $event->getData()['user'] ?? null;
            if ($user) {
                // Actualizar fecha de último login
                $user->updateLastLogin();
            }
        },
    ],
];
```

Este código produce el siguiente error:

```
PHP Fatal error: Constant expression contains invalid operations
```

## Causa del Error

El error ocurre porque en PHP, los valores iniciales de las propiedades de clase deben ser expresiones constantes. Las expresiones constantes solo pueden contener tipos de datos simples (como strings, números, arrays), constantes definidas, y expresiones simples que operan con estos tipos.

Las funciones anónimas (closures) son objetos en PHP, no valores constantes, por lo que no pueden utilizarse como valores iniciales de propiedades.

## Solución

La solución es registrar los closures en el método `registerServices` en lugar de intentar definirlos en la propiedad `$listen`:

```php
protected array $listen = [
    'user.registered' => [
        \App\Events\Listeners\SendWelcomeEmailListener::class,
    ],
    // ELIMINAR closures de aquí
];

public function registerServices($container)
{
    // Llamar al método padre primero para registrar los listeners basados en clases
    parent::registerServices($container);
    
    // Obtener el dispatcher de eventos
    $dispatcher = $container->get(EventDispatcherContract::class);
    
    // Registrar los listeners basados en closures AQUÍ
    $dispatcher->listen('user.login', function ($event) {
        // Lógica para manejar el inicio de sesión
        $user = $event->getData()['user'] ?? null;
        if ($user) {
            // Actualizar fecha de último login
            $user->updateLastLogin();
        }
    });
    
    $dispatcher->listen('application.bootstrapped', function ($event) {
        // Lógica para ejecutar cuando la aplicación ha iniciado
    });
}
```

## Ejemplos

### Ejemplo Incorrecto (Generará Error)

```php
protected array $listen = [
    'event.name' => [
        function ($event) { /* código */ },  // ERROR: No es una expresión constante
    ],
];
```

### Ejemplo Correcto

```php
// En la definición de la clase
protected array $listen = [
    'user.registered' => [
        \App\Events\Listeners\SendWelcomeEmailListener::class,  // Correcto: Solo referencias a clases
    ],
];

// En el método registerServices
public function registerServices($container)
{
    parent::registerServices($container);
    
    $dispatcher = $container->get(EventDispatcherContract::class);
    $dispatcher->listen('event.name', function ($event) {
        // Tu lógica aquí
    });
}
```

## Documentación Relacionada

Para más información sobre las mejores prácticas para trabajar con listeners de eventos, consulta:

- [Proveedor de Servicios de Eventos](event-service-provider.md)
- [Mejores Prácticas para Listeners de Eventos](event-listener-best-practices.md)
- [Guía del Sistema de Eventos](events-guide.md)
