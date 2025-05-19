# Server Configuration

The LightWeight framework offers flexible server configuration options, allowing you to customize your application's behavior according to your needs.

## Configuration File

Server configuration is found in `config/server.php`:

```php
return [
    'implementation' => 'native',
    'force_https' => env('SERVER_FORCE_HTTPS', false),
    'force_www' => env('SERVER_FORCE_WWW', false)
];
```

## Available Options

### implementation

Defines which server implementation to use. Currently only supports:

- `native`: Uses PHP's native web server.

### force_https

When set to `true`, all HTTP requests will be automatically redirected to HTTPS.

```php
'force_https' => true
```

This option is especially useful in production environments where secure communication is required.

Example in the `.env` file:
```
SERVER_FORCE_HTTPS=true
```

### force_www

When set to `true`, all requests without the 'www' prefix will be redirected to the same URL but with the 'www' prefix.

```php
'force_www' => true
```

If both `force_https` and `force_www` options are enabled, the redirects will be combined correctly.

Example in the `.env` file:
```
SERVER_FORCE_WWW=true
```

## API Behavior

For compatibility reasons, HTTPS and www redirects are not applied to routes that begin with `/api`. This ensures that API requests are not affected by these settings.

## Use Case Examples

### Secure Site

To ensure all requests use HTTPS:

```php
'force_https' => true,
'force_www' => false
```

This will redirect http://example.com to https://example.com

### Domain with WWW

To standardize all URLs with the 'www' prefix:

```php
'force_https' => false,
'force_www' => true
```

This will redirect http://example.com to http://www.example.com

### Complete Configuration

To force both HTTPS and the 'www' prefix:

```php
'force_https' => true,
'force_www' => true
```

This will redirect:
- http://example.com to https://www.example.com
- http://www.example.com to https://www.example.com
- https://example.com to https://www.example.com
