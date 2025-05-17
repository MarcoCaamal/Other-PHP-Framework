# Exception Handling with Monolog

LightWeight provides a robust exception handling system with integrated logging using Monolog. This document explains how the exception handling system works and how you can customize it for your application.

## Overview

Exception handling in LightWeight is managed through several components:

1. **ExceptionHandler** - Base handler for catching and processing exceptions
2. **Monolog Integration** - Provides structured logging for exceptions
3. **Notification System** - For critical exceptions that require immediate attention

## Basic Usage

The framework automatically catches and processes all unhandled exceptions. Depending on the environment and configuration, it will:

1. Log the exception with appropriate context
2. Show detailed error pages in development or generic error pages in production
3. Send notifications for critical exceptions

## Configuration

Exception handling can be configured in `config/exceptions.php`:

```php
return [
    // Whether to display detailed error information
    'debug' => env('APP_DEBUG', false),
    
    // Log settings
    'log' => [
        'channel' => 'daily',
        'max_files' => 30,
        'daily' => true,
        'level' => 'error',
        'path' => 'logs/exceptions.log',
        'critical_path' => 'logs/critical.log',
    ],
    
    // Notification settings
    'notifications' => [
        'channels' => ['log', 'email'],
        'email' => [
            'to' => 'admin@example.com',
        ],
        // Other notification channel configurations...
    ],
    
    // View templates
    'views' => [
        'not_found' => 'errors.404',
        'validation' => 'errors.validation',
        'database' => 'errors.database',
        'general' => 'errors.application',
    ],
];
```

## Custom Exception Handler

You can create a custom exception handler by extending the base handler:

```php
<?php

namespace App\Exceptions;

use LightWeight\Exceptions\ExceptionHandler as BaseHandler;
use Throwable;

class Handler extends BaseHandler
{
    // Exceptions that shouldn't be reported
    protected array $dontReport = [
        // Add exception classes here
    ];
    
    // Register custom exception handlers
    public function register(): void
    {
        // Example: Handle API exceptions
        $this->registerHandler(ApiException::class, function ($e, $request) {
            return Response::json([
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ])->setStatus(400);
        });
    }
}
```

## Logging Exceptions

The framework automatically logs exceptions using Monolog. The log includes:

- Exception type and message
- File and line where the exception occurred
- Stack trace
- Context information

You can access the logger directly if needed:

```php
try {
    // Some code that might throw exceptions
} catch (Throwable $e) {
    logMessage($e->getMessage(), [
        'exception' => get_class($e),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'code' => $e->getCode(),
        'trace' => $e->getTraceAsString(),
    ], 'error');
}
```

## Critical Exception Notifications

For exceptions that require immediate attention, you can:

1. Create a custom exception that extends `CriticalException`
2. Configure notification channels in the exceptions config
3. The framework will automatically send notifications when these exceptions occur

Example:

```php
class DatabaseConnectionException extends CriticalException
{
    // Specify which channels to use for notifications
    public function getNotificationChannels(): array
    {
        return ['log', 'email', 'slack'];
    }
}
```

## Advanced Configuration

### Custom Log Channels

You can add custom log channels by creating a service provider that extends the exception logging system:

```php
class AppExceptionServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $logger = $this->app->get('exception.logger');
        
        // Add a custom handler for database exceptions
        $dbFormatter = new LineFormatter(...);
        $dbHandler = new StreamHandler(...);
        $dbHandler->setFormatter($dbFormatter);
        
        $logger->pushHandler($dbHandler);
    }
}
```

### Custom Exception Renderers

You can customize how exceptions are rendered by implementing custom renderer methods in your exception handler:

```php
protected function renderCustomException(CustomException $e): ResponseContract
{
    return Response::view('errors.custom', [
        'exception' => $e
    ])->setStatus(500);
}
```

## Advanced Topics

### Exception Report Filtering

You can control which exceptions are reported by overriding the `shouldReport` method:

```php
public function shouldReport(Throwable $e): bool
{
    // Don't report validation exceptions
    if ($e instanceof ValidationException) {
        return false;
    }
    
    return parent::shouldReport($e);
}
```

### Custom Notification Channels

To implement custom notification channels, extend the exception handler and implement a custom notification method:

```php
protected function sendNotification(string $channel, array $context, Throwable $e): void
{
    if ($channel === 'custom-channel') {
        // Custom notification logic
        return;
    }
    
    parent::sendNotification($channel, $context, $e);
}
```
