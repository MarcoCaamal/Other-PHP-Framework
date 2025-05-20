# MakeController Command Documentation

The `make:controller` command in the LightWeight PHP framework is used to generate controller files with various options and templates. This command supports creating controllers in subdirectories, with different templates, and includes options to generate related resources such as models, migrations, views, and routes.

## Basic Usage

```
php light.php make:controller ControllerName
```

This will create a basic controller in the `app/Controllers` directory.

## Controller Types

You can specify the type of controller to create using the `--type` or `-t` option:

```
php light.php make:controller ControllerName --type=api
```

Available controller types:
- `basic` (default): A simple controller with no predefined methods
- `api`: An API controller with methods for RESTful endpoints
- `resource`: A resource controller with full CRUD operations
- `web`: A web controller with methods for displaying views

## Subdirectories

You can create controllers in subdirectories by including the directory structure in the controller name:

```
php light.php make:controller Admin/UserController
```

This will create a controller in the `app/Controllers/Admin` directory with the appropriate namespace.

## Associated Resources

### Models

You can create an associated model using the `--model` or `-m` option:

```
php light.php make:controller PostController --model=Post
```

If you don't specify a model name, it will use the controller name without the "Controller" suffix.

The model will be created with the following configurable properties:
- `$table`: The table associated with the model
- `$primaryKey`: The primary key field (default: 'id')
- `$hidden`: Attributes that should be hidden from arrays
- `$fillable`: Attributes that can be mass assigned 
- `$attributes`: Initial attribute values
- `$insertTimestamps`: Whether to automatically insert timestamps (created_at, updated_at)

### Migrations

You can create an associated migration using the `--migration` option:

```
php light.php make:controller UserController --migration
```

This will create a migration file for a table named based on the controller name (e.g., `users` for a `UserController`).

### Views

For web controllers, you can create associated view files using the `--views` or `-w` option:

```
php light.php make:controller ProductController --type=web --views
```

This will create the following view files in the `resources/views/Product` directory:
- `index.php`
- `create.php`
- `show.php`
- `edit.php`

Note that views are only created for controllers of type `web`. If you try to create views for a different controller type, you will see a message indicating that views are only created for web controllers.

### Routes

You can add routes to the application using the `--routes` or `-r` option:

```
php light.php make:controller UserController --type=web --routes
```

This will add routes based on the controller methods to a route file. By default, routes will be added to a file named after the controller type (e.g., `routes/web.php` for web controllers).

You can also specify a custom route file name:

```
php light.php make:controller ProductController --type=api --routes=custom
```

This will add routes to `routes/custom.php`.

Or you can create a new route file specifically for this controller:

```
php light.php make:controller BlogController --type=resource --routes=new
```

This will create a new file named after the controller (e.g., `routes/blog.php`).

Route files will include the appropriate route definitions based on the controller type, for example:

```php
// routes/web.php
use LightWeight\Routing\Route;

Route::get('/products', [App\Controllers\ProductController::class, 'index'])->name('product.index');
Route::get('/products/create', [App\Controllers\ProductController::class, 'create'])->name('product.create');
Route::post('/products', [App\Controllers\ProductController::class, 'store'])->name('product.store');
Route::get('/products/{id}', [App\Controllers\ProductController::class, 'show'])->name('product.show');
Route::get('/products/{id}/edit', [App\Controllers\ProductController::class, 'edit'])->name('product.edit');
Route::put('/products/{id}', [App\Controllers\ProductController::class, 'update'])->name('product.update');
Route::delete('/products/{id}', [App\Controllers\ProductController::class, 'destroy'])->name('product.destroy');
```

Note that routes are not created for controllers of type `basic` since they have no predefined methods.

### Create All Resources

You can create all associated resources at once using the `--all` or `-a` option:

```
php light.php make:controller ShopController --type=resource --all
```

For different controller types, this will create:

- For `web` controllers: controller, model, migration, views, and routes
- For `api` and `resource` controllers: controller, model, migration, and routes
- For `basic` controllers: controller, model, and migration (no views or routes)

## Examples

### Creating an API Controller in a Subdirectory

```
php light.php make:controller Api/ProductController --type=api
```

### Creating a Web Controller with All Resources

```
php light.php make:controller Admin/UserController --type=web --all
```

### Creating a Resource Controller with a Custom Model Name

```
php light.php make:controller BlogController --type=resource --model=Post
```

## Command Options Reference

| Option | Shortcut | Description |
|--------|----------|-------------|
| `--type=<type>` | `-t` | Type of controller to create (basic, api, resource, web) |
| `--model[=<name>]` | `-m` | Create an associated model (optional name) |
| `--migration` | | Create an associated migration |
| `--views` | `-w` | Create associated views (only for web controllers) |
| `--routes[=<name>]` | `-r` | Add routes to a file (provide filename or leave empty for default) |
| `--all` | `-a` | Create all associated resources |
