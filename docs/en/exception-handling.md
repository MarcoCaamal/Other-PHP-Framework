# Exception Handling System

The LightWeight framework provides a robust exception handling system that allows you to:

- Centrally manage and log exceptions through Monolog
- Customize how different types of exceptions are handled
- Provide user-friendly error responses
- Separate API and web error handling logic

## How It Works

When an exception is thrown in your application, the exception handler will:

1. Report the exception to the Monolog logging system (unless configured not to)
2. Render an appropriate response based on the exception type
3. Send the response to the client

## Using the Exception Handler

### Basic Exception Handling

All exceptions in your application are automatically routed through the exception handler. The default handler will:

1. Show a user-friendly error page in production
2. Show detailed error information when in debug mode
3. Return API-friendly JSON responses for API requests

### Customizing the Handler

You can customize exception handling by editing your application's exception handler in `app/Exceptions/Handler.php`.

```php
namespace App\Exceptions;

use Throwable;
use LightWeight\Exceptions\ExceptionHandler as BaseExceptionHandler;
use LightWeight\Http\Contracts\RequestContract;
use LightWeight\Http\Contracts\ResponseContract;
use LightWeight\Http\Response;

class Handler extends BaseExceptionHandler
{
    /**
     * Exception types that should not be reported
     */
    protected array $dontReport = [
        \LightWeight\Http\HttpNotFoundException::class,
        \LightWeight\Validation\Exceptions\ValidationException::class,
        // Add your custom exceptions here
    ];
    
    /**
     * Register custom exception handlers
     */
    public function register(): void
    {
        // Register a custom handler for your exception
        $this->registerHandler(
            \App\Exceptions\CustomException::class,
            function($e, $request) {
                // Return a custom response
                return Response::view('errors.custom', [
                    'message' => $e->getMessage()
                ])->setStatus(500);
            }
        );
    }
}
```

### Creating Custom Exception Classes

You can create your own exception types by extending the `LightWeightException` class:

```php
namespace App\Exceptions;

use LightWeight\Exceptions\LightWeightException;

class CustomException extends LightWeightException
{
    public function __construct(string $message = "A custom error occurred", int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
```

### Specialized Exception Types

The framework includes specialized exception types for common scenarios. You can create your own custom exception types by extending the `LightWeightException` class.

## Exception Reporting

### Configuring Exception Logging

You can configure exception logging in `config/exceptions.php`:

```php
return [
    'exception_handler' => \App\Exceptions\Handler::class,
    'debug' => env('APP_DEBUG', false),
    'log' => [
        'channel' => 'daily',
        'max_files' => 30,
        'path' => 'logs/exceptions.log',
        'critical_path' => 'logs/critical.log',
        'level' => 'error',
    ],
];
```

### Preventing Exceptions from Being Reported

If you want to prevent certain exception types from being logged, add them to the `$dontReport` array in your exception handler:

```php
protected array $dontReport = [
    \LightWeight\Http\HttpNotFoundException::class,
    \App\Exceptions\CustomException::class,
];
```

## Error Views

The framework includes several error view templates:

- `errors/404.php` - For "Not Found" errors
- `errors/application.php` - For general application errors
- `errors/database.php` - For database-related errors
- `errors/validation.php` - For validation errors

You can customize these views or create new ones in your application's `resources/views/errors` directory.

## API Error Responses

For API requests (URIs starting with `/api`), the exception handler will automatically return JSON responses with appropriate HTTP status codes:

```json
{
  "error": "App\\Exceptions\\CustomException",
  "message": "The error message"
}
```

In debug mode, the response will include additional information:

```json
{
  "error": "App\\Exceptions\\CustomException",
  "message": "The error message",
  "file": "/path/to/file.php",
  "line": 42,
  "trace": [...]
}
```

## Exception Notifications

The framework includes a notification system for critical exceptions. When a critical exception occurs, the system can automatically send notifications through various channels:

- Email
- Log
- Slack
- Webhook
- SMS

### Configuring Exception Notifications

You can configure notifications in `config/exceptions.php`:

```php
'notifications' => [
    'channels' => env('EXCEPTION_NOTIFICATION_CHANNELS', 'log,email'),
    'email' => [
        'to' => env('EXCEPTION_EMAIL', 'admin@example.com'),
    ],
    'slack' => [
        'webhook' => env('EXCEPTION_SLACK_WEBHOOK', ''),
    ],
    'webhook' => [
        'url' => env('EXCEPTION_WEBHOOK_URL', ''),
    ],
    'sms' => [
        'to' => env('EXCEPTION_SMS', ''),
        'provider' => env('EXCEPTION_SMS_PROVIDER', 'twilio'),
    ],
],
```

### Using Critical Exceptions

You can create critical exceptions to trigger notifications:

```php
throw new \LightWeight\Exceptions\CriticalException(
    'Database connection failed',
    'database',
    'critical',
    ['email', 'slack']
);
```

## API Exception Middleware

For API routes, you can use the `ApiExceptionHandlerMiddleware` to ensure consistent handling of exceptions:

```php
// In your routes file
$router->group(['prefix' => 'api', 'middleware' => [\LightWeight\Http\Middleware\ApiExceptionHandlerMiddleware::class]], function($router) {
    // API routes
});
```

This middleware will:
1. Catch any exceptions in the API routes
2. Log the exceptions
3. Return consistent JSON responses with appropriate status codes

## Monolog Integration

The LightWeight framework now uses Monolog for robust exception logging. When an exception occurs, the following information is captured:

- Exception class and message
- File and line where the exception occurred
- Stack trace
- Additional context relevant to the exception

The integration with Monolog provides several benefits:

- Structured logging with standardized format
- Multiple output handlers (file, database, external services)
- Configurable log levels for different types of exceptions
- Advanced filtering and processing capabilities

For more details on the Monolog integration, see [Exception Handling with Monolog](exception-handling-with-monolog.md).

## Best Practices

1. Create specific exception types for different error scenarios
2. Use custom exception handlers for complex error handling logic
3. Keep error views simple and user-friendly
4. Provide clear error messages that don't expose sensitive information
5. Use appropriate HTTP status codes for API responses

> 🌐 [Documentación en Español](../es/exception-handling.md)
