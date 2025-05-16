# Evento Router.Matched

> 🌐 [English Documentation](../en/router-matched-event.md)

El evento `router.matched` se dispara cuando el router de LightWeight encuentra una ruta que coincide con la solicitud actual. Este evento proporciona información útil sobre la ruta coincidente, permitiendo ejecutar código específico cuando se accede a ciertas rutas.

## Cuándo se dispara

El evento `router.matched` se dispara durante el proceso de resolución de rutas, justo después de que se ha encontrado una coincidencia, pero antes de que se ejecute el controlador o la acción de la ruta.

## Datos del evento

El evento `RouterMatched` proporciona los siguientes métodos para acceder a la información de la ruta:

- `getRoute()`: Devuelve la instancia de `Route` que coincidió con la solicitud
- `getUri()`: Devuelve la URI solicitada
- `getMethod()`: Devuelve el método HTTP utilizado en la solicitud (GET, POST, etc.)

## Casos de uso comunes

### Registro de acceso a rutas específicas

```php
on('router.matched', function ($event) {
    if (str_starts_with($event->getUri(), '/admin')) {
        app('log')->info(sprintf(
            "Acceso a sección de administración: %s %s",
            $event->getMethod(),
            $event->getUri()
        ));
    }
});
```

### Verificaciones de seguridad adicionales

```php
on('router.matched', function ($event) {
    $route = $event->getRoute();
    
    // Verificación para rutas sensibles
    if (str_starts_with($event->getUri(), '/api/admin')) {
        // Implementa verificaciones adicionales
        $token = request()->header('X-ADMIN-TOKEN');
        if (!app('security')->validateAdminToken($token)) {
            abort(403, 'Acceso denegado');
        }
    }
});
```

### Análisis y métricas

```php
on('router.matched', function ($event) {
    // Registrar estadísticas de uso de rutas
    app('metrics')->increment('route.hits.' . str_replace('/', '.', trim($event->getUri(), '/')));
    
    // Iniciar temporizador para medir rendimiento
    app('timer')->start('route.' . $event->getUri());
});
```

## Notas técnicas

El evento `router.matched` se implementa en la clase `RouterMatched`, que extiende la clase base `Event`. Si necesitas personalizar aún más este evento o agregar funcionalidad adicional, puedes hacerlo extendiendo esta clase.

El evento se dispara automáticamente desde el método `resolveRoute` del router, por lo que no es necesario realizar ninguna configuración adicional para que esté disponible.
