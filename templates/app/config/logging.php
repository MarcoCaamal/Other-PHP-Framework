<?php

/**
 * Logging Configuration
 *
 * This file defines the configuration for the logging system in the LightWeight framework.
 */

return [
    /**
     * Default Logging Channel
     *
     * This option defines the default logging channel that is used when writing
     * messages to the logs. The name specified here should match one of the
     * channels defined in the "channels" configuration array below.
     */
    'default_channel' => env('LOG_CHANNEL', 'daily'),

    /**
     * Log Level
     *
     * This option controls the minimum severity level of messages that are logged.
     * Available options: debug, info, notice, warning, error, critical, alert, emergency
     */
    'level' => env('LOG_LEVEL', 'debug'),

    /**
     * Logging Channels
     *
     * Here you may configure the logging channels for your application.
     * Available drivers: "single", "daily", "slack", "syslog", "errorlog", "custom"
     *
     * Supported options:
     * - path: Path to the log file
     * - level: Minimum level to log
     * - days: Number of days to keep logs (when using daily)
     * - bubble: Whether messages should bubble up to other handlers
     * - format: Log format
     * - date_format: Date format for logs
     * - handlers: Array of custom handlers to add
     */
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

        'stdout' => [
            'path' => 'php://stdout',
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'stderr' => [
            'path' => 'php://stderr',
            'level' => env('LOG_LEVEL', 'error'),
        ],

        'emergency' => [
            'path' => storagePath('logs/emergency.log'),
            'level' => 'emergency',
        ],
    ],

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
        ],
    ],
];
