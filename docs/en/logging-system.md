# Logging System

> 🌐 [Documentación en Español](../es/logging-system.md)

The LightWeight framework includes a robust logging system based on [Monolog](https://github.com/Seldaek/monolog), one of the most popular logging libraries for PHP.

## Basic Configuration

The logging system is configured in the `config/logging.php` file. This file defines the available logging channels and the configuration for each one.

```php
return [
    // Default logging channel
    'default_channel' => env('LOG_CHANNEL', 'daily'),

    // Minimum logging level
    'level' => env('LOG_LEVEL', 'debug'),

    // Available logging channels
    'channels' => [
        'single' => [
            'path' => storagePath('logs/lightweight.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'bubble' => true,
        ],
        
        'daily' => [
            'path' => storagePath('logs/lightweight.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 7,
            'bubble' => true,
        ],
        
        // More channels...
    ],
];
```

## Basic Usage

### Using the Helper Functions

The simplest way to use the logging system is through the helper functions:

```php
// Get the logger instance
$logger = logger();

// Log messages with different levels
logger()->debug('Debug message');
logger()->info('General information');
logger()->warning('Warning');
logger()->error('Error');
logger()->critical('Critical error');
logger()->alert('Alert');
logger()->emergency('Emergency');

// Log with additional context
logger()->info('User created', ['id' => $user->id, 'email' => $user->email]);

// Use the logMessage() helper function for a more direct approach
logMessage('Information message'); // Default level: info
logMessage('Error occurred', ['details' => $exception->getMessage()], 'error'); // With level
```

### Using the logger instance

You can also access the logger through the application instance:

```php
// Through the App instance
$logger = app()->log();

// Log messages
$logger->info('Information message');
$logger->error('Error message', ['context' => 'value']);
```

## Configuring custom handlers

You can configure additional handlers for your logger using the `pushHandler()` method:

```php
use Monolog\Handler\SlackWebhookHandler;
use Monolog\Level;

// Get the logger instance
$logger = logger();

// Add a Slack handler for critical errors
$logger->pushHandler(
    new SlackWebhookHandler(
        'https://hooks.slack.com/services/T00000000/B00000000/XXXXXXXXXXXXXXXXXXXXXXXX',
        '#errors',
        'LightWeight Error Bot',
        true,
        null,
        false,
        false,
        Level::Critical
    )
);

// Now critical errors will also be sent to Slack
$logger->critical('Critical error in production', ['user_id' => 123]);
```

## Event Logging

The framework includes support for automatic event logging through the `EventServiceProvider`. This can be configured in the `config/logging.php` file:

```php
return [
    // ... other settings

    /**
     * Event Logging Configuration
     *
     * Settings for automatic logging of events dispatched in the application.
     */
    'event_logging' => [
        /**
         * Enable event logging.
         */
        'enabled' => env('LOG_EVENTS', false),
        
        /**
         * Events that should not be logged even when event logging is enabled.
         */
        'excluded_events' => [
            'application.bootstrapped',
            'router.matched',
            // Other events to exclude...
        ],
    ],
];
```

When enabled, the framework will automatically log every event dispatched in the system. The event logging system:

1. Captures all events through a special listener registered with the event dispatcher
2. Formats event data in a consistent way
3. Records events through the configured logger
4. Intelligently handles both `EventContract` implementing events and other event types

This is particularly useful for:

- Debugging event-driven systems
- Tracking user activities
- Monitoring system operations
- Auditing application behavior

To prevent excessive logging, you can add frequently triggered events to the `excluded_events` list.

## Available Handlers in Monolog

Monolog includes numerous handlers that you can use to send your logs to different destinations:

- **StreamHandler**: Writes logs to files or streams (PHP streams)
- **RotatingFileHandler**: Automatically rotates log files (daily, weekly, etc.)
- **SlackWebhookHandler**: Sends logs to a Slack channel via webhook
- **TelegramBotHandler**: Sends logs to a Telegram bot
- **FirePHPHandler**: Sends logs to FirePHP (useful for debugging)
- **ChromePHPHandler**: Sends logs to Chrome Logger
- **NativeMailerHandler**: Sends logs by email
- **SymfonyMailerHandler**: Sends logs using Symfony Mailer
- **ElasticsearchHandler**: Stores logs in Elasticsearch
- **RedisHandler**: Stores logs in Redis
- **MongoDBHandler**: Stores logs in MongoDB

Check the [official Monolog documentation](https://github.com/Seldaek/monolog/blob/main/doc/02-handlers-formatters-processors.md) for more details on these handlers and how to configure them.

## Processors and Formatters

In addition to handlers, Monolog allows you to customize the format of logs and add additional information through processors:

### Custom Formatters

```php
use Monolog\Formatter\LineFormatter;

// Custom format
$format = "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";
$formatter = new LineFormatter($format);

// Apply the formatter to a handler
$handler = new StreamHandler(storagePath('logs/custom.log'));
$handler->setFormatter($formatter);

logger()->pushHandler($handler);
```

### Processors

Processors allow you to add additional information to all records:

```php
use Monolog\Processor\WebProcessor;
use Monolog\Processor\IntrospectionProcessor;

// Add web information (IP, URL, etc.)
logger()->getLogger()->pushProcessor(new WebProcessor());

// Add information about where the logger was called from
logger()->getLogger()->pushProcessor(new IntrospectionProcessor());
```

## Logging Best Practices

1. **Use the appropriate level**: Use different levels according to the importance of the message.
2. **Include context**: Add associative arrays with relevant information.
3. **Clear messages**: Write descriptive and coherent messages.
4. **Avoid sensitive information**: Don't log passwords, tokens, etc.
5. **Rotate your logs**: Use RotatingFileHandler to avoid files that are too large.
6. **Configure according to the environment**: Use different levels and handlers depending on the environment (dev, test, prod).

## References

- [Monolog Documentation](https://github.com/Seldaek/monolog/blob/main/README.md)
- [PSR-3: Logger Interface](https://www.php-fig.org/psr/psr-3/)
