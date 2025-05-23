<?php

namespace App\Exceptions;

use Throwable;
use LightWeight\Exceptions\ExceptionHandler as BaseExceptionHandler;
use LightWeight\Http\Contracts\RequestContract;
use LightWeight\Http\Contracts\ResponseContract;
use LightWeight\Http\Response;

/**
 * Application exception handler
 */
class Handler extends BaseExceptionHandler
{
    /**
     * Exception types that should not be reported
     *
     * @var array<class-string<Throwable>>
     */
    protected array $dontReport = [
        \LightWeight\Http\HttpNotFoundException::class,
        \LightWeight\Validation\Exceptions\ValidationException::class,
        // Add application specific exceptions here
    ];

    /**
     * Report an exception
     *
     * @param Throwable $e
     * @return void
     */
    public function report(Throwable $e): void
    {
        // Check if we should report this exception
        if (!$this->shouldReport($e)) {
            return;
        }

        // Use application logging channel if configured
        $channel = config('exceptions.log.channel', 'daily');

        // Log the exception with appropriate level based on exception type
        $severity = $this->getSeverityLevel($e);
        error_log("[$severity] " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
        error_log($e->getTraceAsString());

        // You can integrate with more advanced logging systems here
    }

    /**
     * Get the severity level for the exception
     *
     * @param Throwable $e
     * @return string
     */
    protected function getSeverityLevel(Throwable $e): string
    {
        if ($e instanceof \LightWeight\Database\Exceptions\DatabaseException) {
            return 'ERROR';
        }

        if ($e instanceof \LightWeight\Http\HttpNotFoundException) {
            return 'NOTICE';
        }

        // Default severity for unknown exceptions
        return 'WARNING';
    }

    /**
     * Register custom exception handlers
     *
     * @return void
     */
    public function register(): void
    {
        // Register handler for application exceptions
        $this->registerHandler(
            \App\Exceptions\ApplicationException::class,
            function ($e, $request) {
                if (config('exceptions.debug', false) === true) {
                    return Response::view('errors.application', [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTrace()
                    ])->setStatus(500);
                }

                return Response::view('errors.application', [
                    'message' => $e->getMessage() ?: 'An application error has occurred.'
                ])->setStatus(500);
            }
        );

        // You can register more custom handlers here
    }
}
