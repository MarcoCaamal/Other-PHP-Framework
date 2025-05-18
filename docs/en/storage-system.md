# File Storage System

The LightWeight framework includes a versatile storage system that allows you to store and manipulate files efficiently.

## Configuration

The storage system configuration is found in `config/storage.php`:

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

## Available Drivers

The system includes several drivers for different needs:

- **disk**: The basic driver that stores files on disk.
- **local**: For private file storage (no public URL).
- **public**: For files that should always be publicly accessible.

## Basic Usage

### Storing a file

```php
// Store a simple file
$url = Storage::put('path/to/file.txt', 'File content');

// Store with specific visibility
$url = Storage::putWithVisibility('path/to/file.txt', 'Content', 'private');

// Convenience methods
$url = Storage::putPublic('file.txt', 'Content');
$path = Storage::putPrivate('document.txt', 'Confidential content');
```

### Reading a file

```php
// Check if a file exists
if (Storage::exists('path/to/file.txt')) {
    // Read the content
    $content = Storage::get('path/to/file.txt');
}
```

### Deleting a file

```php
Storage::delete('path/to/file.txt');
```

### Working with directories

```php
// Create a directory
Storage::makeDirectory('folder/subfolder');

// List files
$files = Storage::files('folder');

// List directories
$directories = Storage::directories('folder');

// Delete a directory
Storage::deleteDirectory('folder', true); // true to delete recursively
```

### File visibility

```php
// Get visibility
$visibility = Storage::getVisibility('file.txt');

// Set visibility
Storage::setVisibility('file.txt', 'public');

// Convenience methods
Storage::makePublic('file.txt');
Storage::makePrivate('file.txt');
```

## Working with uploaded files

The framework provides an `UploadedFile` class to facilitate handling uploaded files:

```php
// Get an uploaded file
$file = UploadedFile::get('avatar');

// Get multiple files
$files = UploadedFile::getMultiple('photos');

// Validate a file
$isValid = UploadedFile::validate($file, [
    'extensions' => ['jpg', 'png', 'gif'],
    'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
    'maxSize' => 2 * 1024 * 1024, // 2MB
]);

// Store an uploaded file
if ($file) {
    // With unique name
    $url = $file->store('avatars');
    
    // With original name
    $url = $file->storeWithOriginalNameAndVisibility('avatars', 'public');
    
    // With specific visibility
    $url = $file->storePublic('avatars');
    $path = $file->storePrivate('documents');
}
```

## Image manipulation

For image manipulation, the `Image` class is provided:

```php
// Create an image from a file
$image = Image::make('path/to/image.jpg');

// Or from an uploaded file
$file = UploadedFile::get('photo');
$image = new Image($file);

// Resize
$image->resize(800, 600);

// Crop
$image->crop(300, 300, 50, 50);

// Save
$url = $image->store('images/processed.jpg', 90);
```

## Advanced Usage

### Switching between different drivers

```php
// Use a specific driver
$url = Storage::put('file.txt', 'Content', 'public');

// Or get the driver directly
$driver = Storage::driver('local');
$path = $driver->put('file.txt', 'Content');
```

### Copying and moving files

```php
// Copy files
Storage::copy('original.txt', 'copy.txt');

// Move files
Storage::move('source.txt', 'destination.txt');

// Between different drivers
Storage::copy('file.txt', 'public_file.txt', 'local', 'public');
```

### Symbolic links

```php
// Create a symbolic link
Storage::symlink('private_file.txt', 'public_link.txt', 'local', 'public');

// Check if it's a link
$isLink = Storage::isSymlink('public_link.txt');

// Read the link target
$target = Storage::readlink('public_link.txt');
```
