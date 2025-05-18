# Sistema de Almacenamiento de Archivos

El framework LightWeight incluye un sistema de almacenamiento versátil que permite guardar y manipular archivos de manera eficiente.

## Configuración

La configuración del sistema de almacenamiento se encuentra en `config/storage.php`:

```php
return [
    'default' => env('FILE_STORAGE', 'disk'),

    'drivers' => [
        'disk' => [
            'driver' => 'disk',
            'path' => env('STORAGE_PATH', rootDirectory() . '/storage'),
            'url' => env('APP_URL', 'http://localhost'),
            'storage_uri' => env('STORAGE_URI', 'storage'),
            'visibility' => 'public',
        ],
        
        'local' => [
            'driver' => 'local',
            'path' => env('LOCAL_STORAGE_PATH', rootDirectory() . '/storage/app'),
            'visibility' => 'private',
        ],
        
        'public' => [
            'driver' => 'public',
            'path' => env('PUBLIC_STORAGE_PATH', rootDirectory() . '/storage/public'),
            'url' => env('APP_URL', 'http://localhost'),
            'storage_uri' => env('PUBLIC_STORAGE_URI', 'storage/public'),
            'visibility' => 'public',
        ],
    ],
];
```

## Drivers disponibles

El sistema incluye varios drivers para diferentes necesidades:

- **disk**: El driver básico que almacena archivos en disco.
- **local**: Para almacenamiento de archivos privados (sin URL pública).
- **public**: Para archivos que siempre deben ser accesibles públicamente.

## Uso Básico

### Almacenar un archivo

```php
// Almacenar un archivo simple
$url = Storage::put('path/al/archivo.txt', 'Contenido del archivo');

// Almacenar con visibilidad específica
$url = Storage::putWithVisibility('path/al/archivo.txt', 'Contenido', 'private');

// Métodos de conveniencia
$url = Storage::putPublic('archivo.txt', 'Contenido');
$path = Storage::putPrivate('documento.txt', 'Contenido confidencial');
```

### Leer un archivo

```php
// Comprobar si existe un archivo
if (Storage::exists('path/al/archivo.txt')) {
    // Leer el contenido
    $contenido = Storage::get('path/al/archivo.txt');
}
```

### Eliminar un archivo

```php
Storage::delete('path/al/archivo.txt');
```

### Manipular directorios

```php
// Crear un directorio
Storage::makeDirectory('carpeta/subcarpeta');

// Listar archivos
$archivos = Storage::files('carpeta');

// Listar directorios
$directorios = Storage::directories('carpeta');

// Eliminar un directorio
Storage::deleteDirectory('carpeta', true); // true para eliminar recursivamente
```

### Visibilidad de archivos

```php
// Obtener la visibilidad
$visibilidad = Storage::getVisibility('archivo.txt');

// Establecer la visibilidad
Storage::setVisibility('archivo.txt', 'public');

// Métodos de conveniencia
Storage::makePublic('archivo.txt');
Storage::makePrivate('archivo.txt');
```

## Trabajando con archivos subidos

El framework proporciona una clase `UploadedFile` para facilitar el manejo de archivos subidos:

```php
// Obtener un archivo subido
$file = UploadedFile::get('avatar');

// Obtener múltiples archivos
$files = UploadedFile::getMultiple('photos');

// Validar un archivo
$isValid = UploadedFile::validate($file, [
    'extensions' => ['jpg', 'png', 'gif'],
    'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
    'maxSize' => 2 * 1024 * 1024, // 2MB
]);

// Almacenar un archivo subido
if ($file) {
    // Con nombre único
    $url = $file->store('avatars');
    
    // Con nombre original
    $url = $file->storeWithOriginalNameAndVisibility('avatars', 'public');
    
    // Con visibilidad específica
    $url = $file->storePublic('avatars');
    $path = $file->storePrivate('documentos');
}
```

## Manipulación de imágenes

Para manipular imágenes, se proporciona la clase `Image`:

```php
// Crear una imagen a partir de un archivo
$image = Image::make('path/to/image.jpg');

// O desde un archivo subido
$file = UploadedFile::get('photo');
$image = new Image($file);

// Redimensionar
$image->resize(800, 600);

// Recortar
$image->crop(300, 300, 50, 50);

// Guardar
$url = $image->store('images/processed.jpg', 90);
```

## Uso Avanzado

### Cambiar entre diferentes drivers

```php
// Usar un driver específico
$url = Storage::put('archivo.txt', 'Contenido', 'public');

// O obtener el driver directamente
$driver = Storage::driver('local');
$path = $driver->put('archivo.txt', 'Contenido');
```

### Copiar y mover archivos

```php
// Copiar archivos
Storage::copy('original.txt', 'copia.txt');

// Mover archivos
Storage::move('origen.txt', 'destino.txt');

// Entre diferentes drivers
Storage::copy('archivo.txt', 'archivo_publico.txt', 'local', 'public');
```

### Enlaces simbólicos

```php
// Crear un enlace simbólico
Storage::symlink('archivo_privado.txt', 'enlace_publico.txt', 'local', 'public');

// Comprobar si es un enlace
$esEnlace = Storage::isSymlink('enlace_publico.txt');

// Leer el destino del enlace
$destino = Storage::readlink('enlace_publico.txt');
```
