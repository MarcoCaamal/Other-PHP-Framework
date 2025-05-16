<?php

namespace LightWeight\Exceptions\Contracts;

use LightWeight\Http\Contracts\RequestContract;
use LightWeight\Http\Contracts\ResponseContract;
use Throwable;

/**
 * Interface for exception handlers
 */
interface ExceptionHandlerContract
{
    /**
     * Report an exception (log, notify, etc.)
     *
     * @param Throwable $e The exception to report
     * @return void
     */
    public function report(Throwable $e): void;
    
    /**
     * Render a response for an exception
     *
     * @param RequestContract $request The current request
     * @param Throwable $e The exception to handle
     * @return ResponseContract
     */
    public function render(RequestContract $request, Throwable $e): ResponseContract;
    
    /**
     * Determine if the exception should be reported
     *
     * @param Throwable $e
     * @return bool
     */
    public function shouldReport(Throwable $e): bool;
    
    /**
     * Register custom exception handlers
     * 
     * @return void
     */
    public function register(): void;
    
    /**
     * Get a list of exception types that should not be reported
     * 
     * @return array<class-string<Throwable>>
     */
    public function getDontReport(): array;
}
