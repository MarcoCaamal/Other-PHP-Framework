# Eventos de Modelo

El framework LightWeight proporciona un sistema de eventos para modelos que te permite conectarte al ciclo de vida de tus modelos. Esto es útil para ejecutar código cada vez que un modelo es creado, actualizado o eliminado.

## Eventos Disponibles

Los siguientes eventos están disponibles:

- `model.creating`: Se dispara antes de que un modelo sea creado
- `model.created`: Se dispara después de que un modelo ha sido creado
- `model.updating`: Se dispara antes de que un modelo sea actualizado
- `model.updated`: Se dispara después de que un modelo ha sido actualizado
- `model.deleting`: Se dispara antes de que un modelo sea eliminado
- `model.deleted`: Se dispara después de que un modelo ha sido eliminado

## Uso de Eventos de Modelo

### Registrando Oyentes de Eventos

Puedes registrar oyentes para eventos de modelo en tus proveedores de servicios:

```php
<?php

namespace App\Providers;

use App\Models\User;
use App\Listeners\UserCreatedListener;
use LightWeight\App\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Registrar oyentes de eventos de modelo
        \LightWeight\App\listen('model.created', function ($event) {
            $model = $event->getData()['model'];
            if ($model instanceof User) {
                // Hacer algo cuando se crea un modelo Usuario
                \LightWeight\App\log('info', 'Usuario creado: ' . $model->id);
            }
        });
        
        // También puedes usar clases de oyentes dedicadas
        \LightWeight\App\listen('model.updated', new UserUpdatedListener());
    }
}
```

### Creando Clases de Oyentes

Puedes crear clases de oyentes dedicadas para manejar eventos de modelo:

```php
<?php

namespace App\Listeners;

use LightWeight\Events\Contracts\EventInterface;
use LightWeight\Events\Contracts\ListenerInterface;
use App\Models\User;

class UserCreatedListener implements ListenerInterface
{
    public function handle(EventInterface $event): void
    {
        $model = $event->getData()['model'];
        
        // Solo manejar modelos de Usuario
        if ($model instanceof User) {
            // Por ejemplo, enviar un email de bienvenida
            // O crear registros relacionados
        }
    }
}
```

## Casos de Uso de Ejemplo

### Auditoría de Cambios

Puedes usar eventos de modelo para crear un registro de auditoría de cambios en tus modelos:

```php
<?php

\LightWeight\App\listen('model.updated', function ($event) {
    $model = $event->getData()['model'];
    
    // Crear un registro de auditoría
    \App\Models\AuditLog::create([
        'user_id' => auth()->user()->id ?? null,
        'model_type' => get_class($model),
        'model_id' => $model->getKey(),
        'action' => 'actualizado',
        'data' => json_encode($model->getAttributes())
    ]);
});
```

### Creación de Registros Relacionados

Puedes usar el evento `model.created` para crear automáticamente registros relacionados:

```php
<?php

\LightWeight\App\listen('model.created', function ($event) {
    $model = $event->getData()['model'];
    
    if ($model instanceof \App\Models\User) {
        // Crear automáticamente un perfil para nuevos usuarios
        $model->saveRelated('profile', new \App\Models\Profile([
            'name' => $model->name
        ]));
    }
});
```

### Validación Antes de Guardar

Puedes usar los eventos `model.creating` y `model.updating` para realizar validaciones antes de guardar:

```php
<?php

\LightWeight\App\listen('model.creating', function ($event) {
    $model = $event->getData()['model'];
    
    if ($model instanceof \App\Models\Article) {
        // Realizar validación
        if (empty($model->title)) {
            throw new \Exception('El título del artículo no puede estar vacío');
        }
    }
});
```

## Propagación de Eventos

Los eventos de modelo se propagan a través del sistema de despacho de eventos y pueden ser utilizados con cualquier mecanismo de oyente de eventos proporcionado por el framework. Esto los hace compatibles tanto con oyentes basados en closures como con oyentes basados en clases.

## Consideraciones de Rendimiento

Al usar eventos de modelo, ten en cuenta que pueden añadir sobrecarga a tu aplicación, especialmente si tienes muchos oyentes registrados para eventos comunes como `model.created`. Registra solo los oyentes que realmente necesites para optimizar el rendimiento.
