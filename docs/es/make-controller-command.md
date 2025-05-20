# Documentación del Comando MakeController

El comando `make:controller` en el framework PHP LightWeight se utiliza para generar archivos de controladores con varias opciones y plantillas. Este comando admite la creación de controladores en subdirectorios, con diferentes plantillas, e incluye opciones para generar recursos relacionados como modelos, migraciones, vistas y rutas.

## Uso Básico

```
php light.php make:controller NombreControlador
```

Esto creará un controlador básico en el directorio `app/Controllers`.

## Tipos de Controladores

Puede especificar el tipo de controlador a crear utilizando la opción `--type` o `-t`:

```
php light.php make:controller NombreControlador --type=api
```

Tipos de controladores disponibles:
- `basic` (por defecto): Un controlador simple sin métodos predefinidos
- `api`: Un controlador API con métodos para endpoints RESTful
- `resource`: Un controlador de recursos con operaciones CRUD completas
- `web`: Un controlador web con métodos para mostrar vistas

## Subdirectorios

Puede crear controladores en subdirectorios incluyendo la estructura de directorios en el nombre del controlador:

```
php light.php make:controller Admin/UserController
```

Esto creará un controlador en el directorio `app/Controllers/Admin` con el espacio de nombres correspondiente.

## Recursos Asociados

### Modelos

Puede crear un modelo asociado utilizando la opción `--model` o `-m`:

```
php light.php make:controller PostController --model=Post
```

Si no especifica un nombre de modelo, utilizará el nombre del controlador sin el sufijo "Controller".

El modelo se creará con las siguientes propiedades configurables:
- `$table`: La tabla asociada al modelo
- `$primaryKey`: El campo de clave primaria (predeterminado: 'id')
- `$hidden`: Atributos que deben ocultarse de los arrays
- `$fillable`: Atributos que pueden asignarse masivamente
- `$attributes`: Valores iniciales de atributos
- `$insertTimestamps`: Si se deben insertar automáticamente las marcas de tiempo (created_at, updated_at)

### Migraciones

Puede crear una migración asociada utilizando la opción `--migration`:

```
php light.php make:controller UserController --migration
```

Esto creará un archivo de migración para una tabla nombrada según el nombre del controlador (ej., `users` para un `UserController`).

### Vistas

Para controladores web, puede crear archivos de vista asociados utilizando la opción `--views` o `-w`:

```
php light.php make:controller ProductController --type=web --views
```

Esto creará los siguientes archivos de vista en el directorio `resources/views/Product`:
- `index.php`
- `create.php`
- `show.php`
- `edit.php`

Tenga en cuenta que las vistas solo se crean para controladores de tipo `web`. Si intenta crear vistas para un tipo de controlador diferente, verá un mensaje indicando que las vistas solo se crean para controladores web.

### Rutas

Puede agregar rutas a la aplicación utilizando la opción `--routes` o `-r`:

```
php light.php make:controller UserController --type=web --routes
```

Esto agregará rutas basadas en los métodos del controlador a un archivo de rutas. Por defecto, las rutas se agregarán a un archivo nombrado según el tipo de controlador (ej., `routes/web.php` para controladores web).

También puede especificar un nombre de archivo de rutas personalizado:

```
php light.php make:controller ProductController --type=api --routes=custom
```

Esto agregará rutas a `routes/custom.php`.

O puede crear un nuevo archivo de rutas específicamente para este controlador:

```
php light.php make:controller BlogController --type=resource --routes=new
```

Esto creará un nuevo archivo nombrado según el controlador (ej., `routes/blog.php`).

Los archivos de rutas incluirán las definiciones de rutas apropiadas según el tipo de controlador, por ejemplo:

```php
// routes/web.php
use LightWeight\Routing\Route;

Route::get('/productos', [App\Controllers\ProductController::class, 'index'])->name('producto.index');
Route::get('/productos/crear', [App\Controllers\ProductController::class, 'create'])->name('producto.create');
Route::post('/productos', [App\Controllers\ProductController::class, 'store'])->name('producto.store');
Route::get('/productos/{id}', [App\Controllers\ProductController::class, 'show'])->name('producto.show');
Route::get('/productos/{id}/editar', [App\Controllers\ProductController::class, 'edit'])->name('producto.edit');
Route::put('/productos/{id}', [App\Controllers\ProductController::class, 'update'])->name('producto.update');
Route::delete('/productos/{id}', [App\Controllers\ProductController::class, 'destroy'])->name('producto.destroy');
```

Tenga en cuenta que no se crean rutas para controladores de tipo `basic` ya que no tienen métodos predefinidos.

### Crear Todos los Recursos

Puede crear todos los recursos asociados a la vez utilizando la opción `--all` o `-a`:

```
php light.php make:controller ShopController --type=resource --all
```

Para diferentes tipos de controladores, esto creará:

- Para controladores `web`: controlador, modelo, migración, vistas y rutas
- Para controladores `api` y `resource`: controlador, modelo, migración y rutas
- Para controladores `basic`: controlador, modelo y migración (sin vistas ni rutas)

## Ejemplos

### Creación de un Controlador API en un Subdirectorio

```
php light.php make:controller Api/ProductController --type=api
```

### Creación de un Controlador Web con Todos los Recursos

```
php light.php make:controller Admin/UserController --type=web --all
```

### Creación de un Controlador de Recursos con un Nombre de Modelo Personalizado

```
php light.php make:controller BlogController --type=resource --model=Post
```

## Referencia de Opciones del Comando

| Opción | Atajo | Descripción |
|--------|-------|-------------|
| `--type=<tipo>` | `-t` | Tipo de controlador a crear (basic, api, resource, web) |
| `--model[=<nombre>]` | `-m` | Crear un modelo asociado (nombre opcional) |
| `--migration` | | Crear una migración asociada |
| `--views` | `-w` | Crear vistas asociadas (solo para controladores web) |
| `--routes[=<nombre>]` | `-r` | Agregar rutas a un archivo (proporcione nombre de archivo o deje vacío para el predeterminado) |
| `--all` | `-a` | Crear todos los recursos asociados |
