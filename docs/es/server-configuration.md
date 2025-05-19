# Configuración del Servidor

El framework LightWeight ofrece opciones flexibles de configuración para el servidor web, permitiéndole personalizar el comportamiento de la aplicación de acuerdo a sus necesidades.

## Archivo de Configuración

La configuración del servidor se encuentra en `config/server.php`:

```php
return [
    'implementation' => 'native',
    'force_https' => env('SERVER_FORCE_HTTPS', false),
    'force_www' => env('SERVER_FORCE_WWW', false)
];
```

## Opciones Disponibles

### implementation

Define qué implementación de servidor usar. Actualmente sólo se admite:

- `native`: Utiliza el servidor web de PHP nativo.

### force_https

Cuando se establece en `true`, todas las solicitudes HTTP serán redirigidas automáticamente a HTTPS.

```php
'force_https' => true
```

Esta opción es especialmente útil en entornos de producción donde se requiere que toda la comunicación sea segura.

Ejemplo en el archivo `.env`:
```
SERVER_FORCE_HTTPS=true
```

### force_www

Cuando se establece en `true`, todas las solicitudes sin el prefijo 'www' serán redirigidas a la misma URL pero con el prefijo 'www'.

```php
'force_www' => true
```

Si ambas opciones `force_https` y `force_www` están habilitadas, las redirecciones se combinarán correctamente.

Ejemplo en el archivo `.env`:
```
SERVER_FORCE_WWW=true
```

## Comportamiento con APIs

Por motivos de compatibilidad, las redirecciones para HTTPS y www no se aplican a las rutas que comienzan con `/api`. Esto asegura que las solicitudes a la API no sean afectadas por estas configuraciones.

## Ejemplos de Casos de Uso

### Sitio Seguro

Para asegurar que todas las solicitudes usen HTTPS:

```php
'force_https' => true,
'force_www' => false
```

Esto redirigirá http://ejemplo.com a https://ejemplo.com

### Dominio con WWW

Para estandarizar todas las URLs con el prefijo 'www':

```php
'force_https' => false,
'force_www' => true
```

Esto redirigirá http://ejemplo.com a http://www.ejemplo.com

### Configuración Completa

Para forzar tanto HTTPS como el prefijo 'www':

```php
'force_https' => true,
'force_www' => true
```

Esto redirigirá:
- http://ejemplo.com a https://www.ejemplo.com
- http://www.ejemplo.com a https://www.ejemplo.com
- https://ejemplo.com a https://www.ejemplo.com
