# Mejores Prácticas para Listeners de Eventos

> 🌐 [English Documentation](../en/event-listener-best-practices.md)

Este documento describe las mejores prácticas para trabajar con listeners de eventos en el framework LightWeight.

## Tipos de Listeners de Eventos

LightWeight soporta dos tipos de listeners de eventos:

1. **Listeners basados en clases**: Definidos como clases que son instanciadas por el contenedor
2. **Listeners basados en closures**: Funciones anónimas definidas en línea

## Registrando Listeners Basados en Clases

Los listeners basados en clases deben registrarse en la propiedad `$listen` de tu `AppEventServiceProvider`:

```php
protected array $listen = [
    'user.registered' => [
        \App\Events\Listeners\SendWelcomeEmailListener::class,
        \App\Events\Listeners\CreateUserProfileListener::class,
    ],
    'order.placed' => [
        \App\Events\Listeners\ProcessOrderListener::class,
    ],
];
```

## Registrando Listeners Basados en Closures

Debido a restricciones de PHP, los listeners basados en closures no pueden definirse directamente en la propiedad `$listen`. En su lugar, deben registrarse en el método `registerServices`:

```php
public function registerServices($container)
{
    // Primero registra los listeners basados en clases
    parent::registerServices($container);
    
    // Luego registra los listeners basados en closures
    $dispatcher = $container->get(EventDispatcherInterface::class);
    
    $dispatcher->listen('user.login', function ($event) {
        // Manejar inicio de sesión de usuario
    });
    
    $dispatcher->listen('application.bootstrapped', function ($event) {
        // Manejar aplicación inicializada
    });
}
```

## Cuándo Usar Cada Tipo

### Usa Listeners Basados en Clases Cuando:

- La lógica del listener es compleja
- El listener requiere inyección de dependencias
- La misma lógica debe reutilizarse para múltiples eventos
- Quieres mantener la clase del proveedor limpia y enfocada
- Deseas mejor capacidad de prueba

### Usa Listeners Basados en Closures Cuando:

- La lógica del listener es simple y corta
- La lógica es específica para un solo evento y no será reutilizada
- Quieres mantener código relacionado junto para mejor legibilidad
- El listener no tiene muchas dependencias

## El Error de Expresión Constante

### Entendiendo el Error

Al intentar usar closures dentro de la propiedad `$listen`, PHP genera el siguiente error:

```
PHP Fatal error: Constant expression contains invalid operations
```

Este error ocurre porque en PHP, los valores iniciales de las propiedades de clase deben ser **expresiones constantes**. Las expresiones constantes solo pueden contener tipos escalares (como strings, numbers, arrays literales), constantes, y expresiones con operaciones sencillas entre estos tipos.

Las funciones anónimas (closures) son objetos en PHP, no valores constantes, por lo que no pueden usarse como valores iniciales de propiedades.

### Ejemplo del Error

```php
// Código incorrecto que generará un error fatal
protected array $listen = [
    'user.login' => [
        function ($event) { 
            // El código dentro del closure no importa, el error ocurre
            // porque un closure no puede ser un valor inicial de propiedad
        },  
    ],
];
```

### Solución Correcta

En lugar de intentar usar closures en la propiedad `$listen`, debes registrarlos en el método `registerServices`:

```php
// En AppEventServiceProvider
public function registerServices($container)
{
    // Primero llamar al padre para manejar los listeners basados en clases
    parent::registerServices($container);
    
    // Obtener el dispatcher de eventos
    $dispatcher = $container->get(EventDispatcherInterface::class);
    
    // Registrar los listeners basados en closures
    $dispatcher->listen('user.login', function ($event) {
        // Tu lógica aquí
    });
    
    $dispatcher->listen('application.bootstrapped', function ($event) {
        // Más lógica aquí
    });
}
```

## Consideraciones de Rendimiento

- Los listeners basados en clases son resueltos desde el contenedor, lo que permite inyección de dependencias pero tiene un pequeño costo de rendimiento
- Los listeners basados en closures son ligeramente más rápidos pero no pueden aprovechar la inyección de dependencias del contenedor

Para la mayoría de las aplicaciones, esta diferencia de rendimiento es insignificante. Elige el enfoque que mejor se adapte a tus necesidades de diseño y mantenibilidad.

## Buenas Prácticas Adicionales

1. **Mantén los listeners enfocados**: Cada listener debe tener una única responsabilidad.

2. **Organiza los listeners relacionados**: Si tienes varios listeners relacionados, considera usar un [EventSubscriber](event-service-provider.md#suscriptores-de-eventos).

3. **Evita listeners pesados**: Si un listener realiza operaciones pesadas, considera usar un proceso asíncrono o un trabajo en cola.

4. **Sé consistente**: Decide un enfoque (clases o closures) y úsalo de manera consistente en toda tu aplicación.

5. **Documenta tus listeners**: Añade comentarios explicando qué hace cada listener y por qué es necesario.
