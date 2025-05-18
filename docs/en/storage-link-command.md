# Storage Link Command

The LightWeight framework includes a `storage:link` command that creates a symbolic link from your public directory to `storage/app/public`, making files in this directory accessible via the web.

## Basic Usage

Run the following command in your terminal:

```bash
php light storage:link
```

This will create a symbolic link from `public/{storage_uri}` to `storage/app/public`, where `{storage_uri}` is the value configured in your `config/storage.php` file (default: "uploads").

## Configuration

The destination of the symbolic link is determined by the following configuration options in your `config/storage.php` file:

```php
// First, it checks for this configuration
'drivers' => [
    'public' => [
        // Other settings...
        'storage_uri' => env('PUBLIC_STORAGE_URI', 'uploads'),
    ],
],

// If not found, it falls back to this one
'storage_uri' => env('STORAGE_URI', 'uploads')
```

You can customize the URI by changing these configuration values or by setting the `PUBLIC_STORAGE_URI` or `STORAGE_URI` environment variables.

## Options

- `--force` or `-f`: Force the recreation of the symbolic link if it already exists.

## Troubleshooting

If you encounter issues when creating the symbolic link:

1. **Permissions**: Make sure your web server has write permissions to the public directory.
2. **Windows users**: You may need to run as Administrator or enable Developer Mode to create symbolic links.
3. **Alternative approach**: If symbolic links are not supported in your environment, you can manually create a directory at `public/{storage_uri}` and configure your web server to rewrite storage URLs to your application.

## Example

If your configuration sets `storage_uri` to "media", the command will create a link from `public/media` to `storage/app/public`. This allows you to access files stored in `storage/app/public/images/photo.jpg` through a URL like `https://yourapp.com/media/images/photo.jpg`.
