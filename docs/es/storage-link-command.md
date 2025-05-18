# Comando Storage Link

El framework LightWeight incluye un comando `storage:link` que crea un enlace simbólico desde tu directorio público a `storage/app/public`, haciendo que los archivos en este directorio sean accesibles a través de la web.

## Uso Básico

Ejecuta el siguiente comando en tu terminal:

```bash
php light storage:link
```

Esto creará un enlace simbólico desde `public/{storage_uri}` a `storage/app/public`, donde `{storage_uri}` es el valor configurado en tu archivo `config/storage.php` (predeterminado: "uploads").

## Configuración

El destino del enlace simbólico está determinado por las siguientes opciones de configuración en tu archivo `config/storage.php`:

```php
// Primero, busca esta configuración
'drivers' => [
    'public' => [
        // Otras configuraciones...
        'storage_uri' => env('PUBLIC_STORAGE_URI', 'uploads'),
    ],
],

// Si no la encuentra, utiliza esta como respaldo
'storage_uri' => env('STORAGE_URI', 'uploads')
```

Puedes personalizar la URI cambiando estos valores de configuración o estableciendo las variables de entorno `PUBLIC_STORAGE_URI` o `STORAGE_URI`.

## Opciones

- `--force` o `-f`: Fuerza la recreación del enlace simbólico si ya existe.

## Solución de problemas

Si encuentras problemas al crear el enlace simbólico:

1. **Permisos**: Asegúrate de que tu servidor web tenga permisos de escritura en el directorio público.
2. **Usuarios de Windows**: Es posible que necesites ejecutar como Administrador o habilitar el Modo Desarrollador para crear enlaces simbólicos.
3. **Enfoque alternativo**: Si los enlaces simbólicos no son compatibles en tu entorno, puedes crear manualmente un directorio en `public/{storage_uri}` y configurar tu servidor web para redirigir las URLs de almacenamiento a tu aplicación.

## Ejemplo

Si tu configuración establece `storage_uri` como "media", el comando creará un enlace desde `public/media` a `storage/app/public`. Esto te permitirá acceder a archivos almacenados en `storage/app/public/images/photo.jpg` a través de una URL como `https://tuapp.com/media/images/photo.jpg`.
