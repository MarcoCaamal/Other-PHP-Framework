<?php

namespace LightWeight\Exceptions;

/**
 * Mock class for ExceptionLogger to track if log was called during tests
 */
class MockExceptionLogger extends ExceptionLogger
{
    // Flag to track if log was called
    public static bool $logWasCalled = false;
    
    /**
     * Log an exception
     *
     * @param \Throwable $exception The exception to log
     * @param string $level Log level (error, warning, info, etc.)
     * @return void
     */
    public static function log(\Throwable $exception, string $level = self::ERROR): void
    {
        // Track that log was called
        self::$logWasCalled = true;
        
        // Don't actually log during tests to avoid side effects
    }
    
    /**
     * Reset the tracking flag
     */
    public static function reset(): void
    {
        self::$logWasCalled = false;
    }
}
