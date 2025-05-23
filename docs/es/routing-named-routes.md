# Rutas con Nombre en LightWeight

LightWeight permite asignar nombres a las rutas para facilitar la generación de URLs, especialmente útil cuando la estructura de las rutas cambia pero se mantienen los nombres.

## Asignación de nombres a rutas

Puedes asignar un nombre a cualquier ruta mediante el método `setName()`:

```php
Route::get('/usuarios/{id}/perfil', function($id) {
    // Código para mostrar el perfil
})->setName('user.profile');
```

## Generación de URLs a partir del nombre

### Helper `route()`

La forma más sencilla de generar una URL es con el helper `route()`:

```php
// URL relativa: /usuarios/123/perfil
$url = route('user.profile', ['id' => 123]);

// URL absoluta: http://example.com/usuarios/123/perfil
$urlAbsoluta = route('user.profile', ['id' => 123], true);

// URL absoluta con dominio personalizado: https://mi-sitio.com/usuarios/123/perfil
$urlPersonalizada = route('user.profile', ['id' => 123], true, 'https://mi-sitio.com');
```

### Clase `Route`

También puedes usar métodos estáticos en la clase `Route`:

```php
// URL relativa
$url = Route::url('user.profile', ['id' => 123]);

// URL absoluta
$urlAbsoluta = Route::urlAbsolute('user.profile', ['id' => 123]);

// URL absoluta con dominio personalizado
$urlPersonalizada = Route::urlAbsolute('user.profile', ['id' => 123], 'https://mi-sitio.com');
```

### Clase `Router`

Si tienes acceso a una instancia de `Router`, puedes usar los siguientes métodos:

```php
// URL relativa
$url = $router->generateUrl('user.profile', ['id' => 123]);

// URL absoluta
$urlAbsoluta = $router->generateAbsoluteUrl('user.profile', ['id' => 123]);

// URL absoluta con dominio personalizado
$urlPersonalizada = $router->generateAbsoluteUrl('user.profile', ['id' => 123], 'https://mi-sitio.com');
```

## Obtener una ruta por nombre

Si necesitas acceder a los detalles de una ruta:

```php
// Mediante Router
$route = $router->getRouteByName('user.profile');

// Mediante helper
$route = getRouteByName('user.profile');
```

## Validación de parámetros

El sistema verifica automáticamente que se proporcionen todos los parámetros requeridos. Si falta alguno, se lanzará una excepción `InvalidArgumentException`.

```php
// Esto lanzará una excepción porque falta el parámetro 'id'
$url = route('user.profile');
```

## Configuración de dominio por defecto

El dominio predeterminado para URLs absolutas se obtiene de la configuración `app.url`. Puedes configurarlo en `config/app.php`:

```php
return [
    'url' => 'https://mi-aplicacion.com',
    // otras configuraciones...
];
```
