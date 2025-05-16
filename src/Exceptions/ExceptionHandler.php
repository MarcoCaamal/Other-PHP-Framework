<?php

namespace LightWeight\Exceptions;

use Throwable;
use LightWeight\Http\Contracts\RequestContract;
use LightWeight\Http\Contracts\ResponseContract;
use LightWeight\Http\HttpNotFoundException;
use LightWeight\Http\Response;
use LightWeight\Validation\Exceptions\ValidationException;
use LightWeight\Database\Exceptions\DatabaseException;
use LightWeight\Exceptions\Contracts\ExceptionHandlerContract;
use LightWeight\Exceptions\ExceptionLogger;

/**
 * Base exception handler class for the application
 */
abstract class ExceptionHandler implements ExceptionHandlerContract
{
    /**
     * Exception types that should not be reported
     *
     * @var array<class-string<Throwable>>
     */
    protected array $dontReport = [
        HttpNotFoundException::class,
        ValidationException::class,
    ];
    
    /**
     * Custom exception handlers
     *
     * @var array<class-string<Throwable>, callable>
     */
    protected array $handlers = [];
    
    /**
     * Report an exception
     *
     * @param Throwable $e
     * @return void
     */
    public function report(Throwable $e): void
    {
        if (!$this->shouldReport($e)) {
            return;
        }
        
        // Determine log level based on exception type
        $level = $this->getLogLevel($e);
        
        // Log the exception
        ExceptionLogger::log($e, $level);
        
        // Check if this is a critical exception that needs notification
        if ($this->isCriticalException($e)) {
            $this->notifyCriticalException($e);
        }
    }
    
    /**
     * Get the log level for the exception
     *
     * @param Throwable $e
     * @return string
     */
    protected function getLogLevel(Throwable $e): string
    {
        if ($e instanceof DatabaseException) {
            return ExceptionLogger::ERROR;
        }
        
        if ($e instanceof HttpNotFoundException) {
            return ExceptionLogger::NOTICE;
        }
        
        if ($e instanceof ValidationException) {
            return ExceptionLogger::WARNING;
        }
        
        // Check for critical exceptions (using string comparison to avoid direct dependency)
        if (strpos(get_class($e), 'CriticalException') !== false) {
            return ExceptionLogger::CRITICAL;
        }
        
        return ExceptionLogger::ERROR;
    }
    
    /**
     * Determine if the exception is critical and needs immediate notification
     *
     * @param Throwable $e
     * @return bool
     */
    protected function isCriticalException(Throwable $e): bool
    {
        // Check for specific critical exception class
        if (strpos(get_class($e), 'CriticalException') !== false) {
            return true;
        }
        
        // Check for database exceptions that might be critical
        if ($e instanceof DatabaseException && $e->getCode() >= 1000) {
            return true;
        }
        
        // Add any other conditions for critical exceptions
        
        return false;
    }
    
    /**
     * Send notifications for critical exceptions
     *
     * @param Throwable $e
     * @return void
     */
    protected function notifyCriticalException(Throwable $e): void
    {
        // If this is a special critical exception with channels specified
        if (method_exists($e, 'getNotificationChannels')) {
            ExceptionNotifier::notify($e, $e->getNotificationChannels());
        } else {
            // Use default notification channels from config
            $channels = config('exceptions.notifications.channels', ['log', 'email']);
            ExceptionNotifier::notify($e, $channels);
        }
    }
    
    /**
     * Determine if the exception should be reported
     *
     * @param Throwable $e
     * @return bool
     */
    public function shouldReport(Throwable $e): bool
    {
        return !$this->isInDontReportList($e);
    }
    
    /**
     * Check if the exception is in the don't report list
     *
     * @param Throwable $e
     * @return bool
     */
    protected function isInDontReportList(Throwable $e): bool
    {
        foreach ($this->getDontReport() as $type) {
            if ($e instanceof $type) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Get the list of exceptions that should not be reported
     *
     * @return array<class-string<Throwable>>
     */
    public function getDontReport(): array
    {
        return $this->dontReport;
    }
    
    /**
     * Render a response for the given exception
     *
     * @param RequestContract $request
     * @param Throwable $e
     * @return ResponseContract
     */
    public function render(RequestContract $request, Throwable $e): ResponseContract
    {
        // Check if we have a custom handler for this exception type
        foreach ($this->handlers as $type => $handler) {
            if ($e instanceof $type) {
                return $handler($e, $request);
            }
        }
        
        // If no custom handler, use default handling based on exception type
        return match(true) {
            $e instanceof HttpNotFoundException => $this->renderHttpNotFound($e),
            $e instanceof ValidationException => $this->renderValidationException($request, $e),
            $e instanceof DatabaseException => $this->renderDatabaseException($e),
            default => $this->renderGenericException($e)
        };
    }
    
    /**
     * Register exception type handler
     *
     * @param class-string<Throwable> $exceptionClass
     * @param callable $handler
     * @return $this
     */
    protected function registerHandler(string $exceptionClass, callable $handler): self
    {
        $this->handlers[$exceptionClass] = $handler;
        return $this;
    }
    
    /**
     * Render a response for HttpNotFoundException
     *
     * @param HttpNotFoundException $e
     * @return ResponseContract
     */
    protected function renderHttpNotFound(HttpNotFoundException $e): ResponseContract
    {
        $view = config('exceptions.views.not_found', 'errors.404');
        return Response::view($view)->setStatus(404);
    }
    
    /**
     * Render a response for ValidationException
     *
     * @param RequestContract $request
     * @param ValidationException $e
     * @return ResponseContract
     */
    protected function renderValidationException(RequestContract $request, ValidationException $e): ResponseContract
    {
        $isApi = str_starts_with($request->uri(), '/api');
        
        if ($isApi) {
            return Response::json([
                'errors' => $e->errors(),
                'message' => "Validation Errors",
            ])->setStatus(422);
        } else {
            // For web requests, show validation error template and keep previous input in session
            session()->set('_errors', $e->errors());
            session()->set('_old', $request->data() ?? []);
            
            $view = config('exceptions.views.validation', 'errors.validation');
            return Response::view($view, [
                'errors' => $e->errors()
            ])->setStatus(422);
        }
    }
    
    /**
     * Render a response for DatabaseException
     *
     * @param DatabaseException $e
     * @return ResponseContract
     */
    protected function renderDatabaseException(DatabaseException $e): ResponseContract
    {
        $view = config('exceptions.views.database', 'errors.database');
        
        if (config('exceptions.debug', false) === true) {
            return Response::view($view, [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace()
            ])->setStatus(500);
        }
        
        return Response::view($view, [
            'message' => 'A database error has occurred.'
        ])->setStatus(500);
    }
    
    /**
     * Render a response for generic exceptions
     *
     * @param Throwable $e
     * @return ResponseContract
     */
    protected function renderGenericException(Throwable $e): ResponseContract
    {
        $view = config('exceptions.views.general', 'errors.application');
        
        if (config('exceptions.debug', false) === true) {
            return Response::view($view, [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace()
            ])->setStatus(500);
        }
        
        return Response::view($view, [
            'message' => 'An unexpected error occurred.'
        ])->setStatus(500);
    }
    
    /**
     * Create a standardized response for an exception
     *
     * @param Throwable $e
     * @param int $status
     * @param string $message
     * @return ResponseContract
     */
    protected function createExceptionResponse(Throwable $e, int $status, string $message): ResponseContract
    {
        $data = [
            'error' => $e::class,
            'message' => $e->getMessage()
        ];
        
        if (config('exceptions.debug', false) === true) {
            $data['file'] = $e->getFile();
            $data['line'] = $e->getLine();
            $data['trace'] = $e->getTrace();
        }
        
        return Response::json($data)->setStatus($status);
    }
}
