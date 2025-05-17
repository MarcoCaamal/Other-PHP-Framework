<?php

namespace LightWeight\Database\Exceptions;

use LightWeight\Exceptions\CriticalException;

/**
 * Exception for database connection failures
 */
class DatabaseConnectionException extends CriticalException
{
    /**
     * Create a new database connection exception
     *
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(
        string $message = "Failed to connect to database",
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            $message,
            'database',
            'critical',
            ['log', 'email', 'slack'],
            $code,
            $previous
        );
    }
}
