<?php

namespace LightWeight\Exceptions\Contracts;

use Throwable;

/**
 * Contract for bootstrap exception handler
 */
interface BootstrapExceptionHandlerContract
{
    /**
     * Log a bootstrap exception
     *
     * @param Throwable $exception The exception to log
     * @return void
     */
    public function logException(Throwable $exception): void;
    
    /**
     * Handle a bootstrap exception
     *
     * @param Throwable $exception The exception to handle
     * @return void
     */
    public function handleException(Throwable $exception): void;
}
