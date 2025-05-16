<?php

namespace App\Exceptions;

use LightWeight\Exceptions\LightWeightException;

/**
 * Base exception for application-specific exceptions
 */
class ApplicationException extends LightWeightException
{
    /**
     * Create a new application exception instance
     *
     * @param string $message The exception message
     * @param int $code The exception code
     * @param \Throwable|null $previous The previous exception
     */
    public function __construct(string $message = "", int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
