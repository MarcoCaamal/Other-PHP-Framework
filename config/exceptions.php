<?php

return [
    /**
     * Application exception handler
     * 
     * This class will handle exceptions thrown during the application execution
     */
    'exception_handler' => \App\Exceptions\Handler::class,
    
    /**
     * Whether to display detailed error information
     *
     * When true, detailed error information will be displayed including file paths,
     * line numbers, and stack traces. This should be set to false in production.
     */
    'debug' => env('APP_DEBUG', false),
    
    /**
     * Logging options for exceptions
     */
    'log' => [
        /**
         * Log channel to use for exceptions
         * Options: single, daily, errorlog, syslog
         */
        'channel' => env('LOG_EXCEPTION_CHANNEL', 'daily'),
        
        /**
         * Maximum number of log files to keep (for daily channel)
         */
        'max_files' => env('LOG_EXCEPTION_MAX_FILES', 30),
        
        /**
         * Whether to use daily log files (with date suffix)
         * If false, a single log file will be used
         */
        'daily' => env('LOG_EXCEPTION_DAILY', true),
        
        /**
         * Log level for notifications/reporting services
         * Options: debug, info, notice, warning, error, critical, alert, emergency
         */
        'level' => env('LOG_EXCEPTION_LEVEL', 'error'),
        
        /**
         * Path to the exception log file
         */
        'path' => env('LOG_EXCEPTION_PATH', 'logs/exceptions.log'),
        
        /**
         * Separate log file for critical exceptions
         */
        'critical_path' => env('LOG_CRITICAL_PATH', 'logs/critical.log'),
    ],
    
    /**
     * Views used for rendering exception responses
     */
    'views' => [
        /**
         * View for 404 Not Found errors
         */
        'not_found' => 'errors.404',
        
        /**
         * View for validation errors
         */
        'validation' => 'errors.validation',
        
        /**
         * View for database errors
         */
        'database' => 'errors.database',
        
        /**
         * View for general application errors
         */
        'general' => 'errors.application',
    ],
    
    /**
     * Notification settings for critical exceptions
     */
    'notifications' => [
        /**
         * Enabled notification channels
         * Options: log, email, slack, webhook, sms
         */
        'channels' => env('EXCEPTION_NOTIFICATION_CHANNELS', 'log,email'),
        
        /**
         * Email notification settings
         */
        'email' => [
            'to' => env('EXCEPTION_EMAIL', 'admin@example.com'),
        ],
        
        /**
         * Slack notification settings
         */
        'slack' => [
            'webhook' => env('EXCEPTION_SLACK_WEBHOOK', ''),
        ],
        
        /**
         * Generic webhook notification settings
         */
        'webhook' => [
            'url' => env('EXCEPTION_WEBHOOK_URL', ''),
        ],
        
        /**
         * SMS notification settings
         */
        'sms' => [
            'to' => env('EXCEPTION_SMS', ''),
            'provider' => env('EXCEPTION_SMS_PROVIDER', 'twilio'),
        ],
    ],
];
