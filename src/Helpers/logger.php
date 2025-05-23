<?php

use LightWeight\Log\Contracts\LoggerContract;

/**
 * Get the logger instance
 *
 * @return LoggerContract
 */
function logger(): LoggerContract
{
    return app()->has(LoggerContract::class)
        ? app(LoggerContract::class)
        : app('logger');
}

/**
 * Log a message to the application log
 *
 * @param string $message Message to log
 * @param array $context Context data
 * @param string $level Log level
 * @return void
 */
function logMessage(string $message, array $context = [], string $level = 'info'): void
{
    logger()->log($level, $message, $context);
}
