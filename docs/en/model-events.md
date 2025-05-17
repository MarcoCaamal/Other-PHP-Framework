# Model Events

LightWeight framework provides a system for model events that allow you to hook into the lifecycle of your models. This is useful for executing code whenever a model is created, updated, or deleted.

## Available Events

The following events are available:

- `model.creating`: Fired before a model is created
- `model.created`: Fired after a model is created
- `model.updating`: Fired before a model is updated
- `model.updated`: Fired after a model is updated
- `model.deleting`: Fired before a model is deleted
- `model.deleted`: Fired after a model is deleted

## Using Model Events

### Registering Event Listeners

You can register listeners for model events in your service providers:

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
        // Register model event listeners
        \LightWeight\App\listen('model.created', function ($event) {
            $model = $event->getData()['model'];
            if ($model instanceof User) {
                // Do something when a User model is created
                \LightWeight\App\log('info', 'User created: ' . $model->id);
            }
        });
        
        // You can also use dedicated listener classes
        \LightWeight\App\listen('model.updated', new UserUpdatedListener());
    }
}
```

### Creating Listener Classes

You can create dedicated listener classes to handle model events:

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
        
        // Only handle User models
        if ($model instanceof User) {
            // For example, send a welcome email
            // Or create related records
        }
    }
}
```

## Example Use Cases

### Auditing Changes

You can use model events to create an audit log of changes to your models:

```php
<?php

\LightWeight\App\listen('model.updated', function ($event) {
    $model = $event->getData()['model'];
    
    // Create an audit record
    \App\Models\AuditLog::create([
        'user_id' => auth()->user()->id ?? null,
        'model_type' => get_class($model),
        'model_id' => $model->getKey(),
        'action' => 'updated',
        'data' => json_encode($model->getAttributes())
    ]);
});
```

### Creating Related Records

You can use the `model.created` event to automatically create related records:

```php
<?php

\LightWeight\App\listen('model.created', function ($event) {
    $model = $event->getData()['model'];
    
    if ($model instanceof \App\Models\User) {
        // Automatically create a profile for new users
        $model->saveRelated('profile', new \App\Models\Profile([
            'name' => $model->name
        ]));
    }
});
```

### Validating Before Save

You can use the `model.creating` and `model.updating` events to perform validation before saving:

```php
<?php

\LightWeight\App\listen('model.creating', function ($event) {
    $model = $event->getData()['model'];
    
    if ($model instanceof \App\Models\Article) {
        // Perform validation
        if (empty($model->title)) {
            throw new \Exception('Article title cannot be empty');
        }
    }
});
```

## Event Propagation

Model events are propagated through the event dispatcher system and can be used with any event listener mechanism provided by the framework. This makes them compatible with both closure-based listeners and class-based listeners.

## Performance Considerations

When using model events, be aware that they can add overhead to your application, especially if you have many listeners registered for common events like `model.created`. Only register the listeners you actually need to optimize performance.
