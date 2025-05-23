<?php

namespace LightWeight\Http\Middleware;

use Closure;
use Throwable;
use LightWeight\Http\Contracts\RequestContract;
use LightWeight\Http\Contracts\ResponseContract;
use LightWeight\Http\Contracts\MiddlewareContract;
use LightWeight\Exceptions\Contracts\ExceptionHandlerContract;

/**
 * Middleware for handling API exceptions
 */
class ApiExceptionHandlerMiddleware implements MiddlewareContract
{
    /**
     * The exception handler instance
     *
     * @var ExceptionHandlerContract
     */
    protected ExceptionHandlerContract $exceptionHandler;

    /**
     * Create a new API exception handler middleware
     *
     * @param ExceptionHandlerContract $exceptionHandler
     */
    public function __construct(ExceptionHandlerContract $exceptionHandler)
    {
        $this->exceptionHandler = $exceptionHandler;
    }

    /**
     * Handle the incoming request.
     *
     * @param RequestContract $request
     * @param callable $next
     * @return ResponseContract
     */
    public function handle(RequestContract $request, Closure $next): ResponseContract
    {
        try {
            // Proceed with the request
            return $next($request);
        } catch (Throwable $exception) {
            // Report the exception
            $this->exceptionHandler->report($exception);

            // Return a JSON response for API
            return $this->handleApiException($request, $exception);
        }
    }

    /**
     * Handle API exception and return a consistent JSON response format
     *
     * @param RequestContract $request
     * @param Throwable $exception
     * @return ResponseContract
     */
    protected function handleApiException(RequestContract $request, Throwable $exception): ResponseContract
    {
        // Use exception handler to get a response
        $response = $this->exceptionHandler->render($request, $exception);

        // Ensure we have a JSON response with the correct content type
        $response->setHeader('Content-Type', 'application/json');

        return $response;
    }
}
