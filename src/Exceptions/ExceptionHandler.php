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
        
        // Create context for the log
        $context = [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'code' => $e->getCode(),
            'trace' => $e->getTraceAsString(),
        ];
        
        // Log the exception using the logger
        try {
            if (function_exists('logMessage')) {
                logMessage($e->getMessage(), $context, $level);
            } else {
                // Fallback for early bootstrap when logger might not be available
                $this->fallbackLog($e, $level);
            }
        } catch (\Throwable $logException) {
            // If logging itself fails, write to error_log as last resort
            error_log("Failed to log exception: {$logException->getMessage()}");
            error_log("Original exception: {$e->getMessage()} in {$e->getFile()} on line {$e->getLine()}");
        }
        
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
            return 'error';
        }
        
        if ($e instanceof HttpNotFoundException) {
            return 'notice';
        }
        
        if ($e instanceof ValidationException) {
            return 'warning';
        }
        
        // Check for critical exceptions (using string comparison to avoid direct dependency)
        if (strpos(get_class($e), 'CriticalException') !== false) {
            return 'critical';
        }
        
        return 'error';
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
        // Get channels from exception or config
        $channels = method_exists($e, 'getNotificationChannels')
            ? $e->getNotificationChannels()
            : config('exceptions.notifications.channels', ['log', 'email']);
            
        // Format exception for notification
        $context = $this->formatExceptionForNotification($e);
        
        // Process each notification channel
        foreach ($channels as $channel) {
            $this->sendNotification($channel, $context, $e);
        }
    }
    
    /**
     * Format exception data for notifications
     * 
     * @param Throwable $e
     * @return array
     */
    protected function formatExceptionForNotification(Throwable $e): array
    {
        $env = env('APP_ENV', 'production');
        $appName = config('app.name', 'LightWeight Application');
        
        return [
            'subject' => "[{$appName}] [{$env}] Exception: " . get_class($e),
            'message' => $e->getMessage(),
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'code' => $e->getCode(),
            'trace' => $e->getTraceAsString(),
            'timestamp' => date('Y-m-d H:i:s'),
            'environment' => $env,
            'application' => $appName,
            'server' => $_SERVER['SERVER_NAME'] ?? 'unknown',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        ];
    }
    
    /**
     * Send a notification through the specified channel
     * 
     * @param string $channel
     * @param array $context
     * @param Throwable $e
     * @return void
     */
    protected function sendNotification(string $channel, array $context, Throwable $e): void
    {
        // Critical exceptions should always be logged regardless of other channels
        if ($channel === 'log') {
            if (function_exists('logMessage')) {
                logMessage(
                    "Critical Exception: " . $context['exception'] . " - " . $context['message'],
                    [
                        'exception' => $context['exception'],
                        'file' => $context['file'],
                        'line' => $context['line'],
                        'trace' => $context['trace'],
                        'request_uri' => $context['request_uri'],
                        'request_method' => $context['request_method'],
                    ],
                    'critical'
                );
            } else {
                $this->fallbackLog($e, 'critical');
            }
            return;
        }
        
        // Email notifications
        if ($channel === 'email') {
            $to = config('exceptions.notifications.email.to', '');
            
            if (empty($to)) {
                return;
            }
            
            $subject = $context['subject'];
            
            // Build email body
            $body = "An exception occurred in your application.\n\n";
            $body .= "Exception: {$context['exception']}\n";
            $body .= "Message: {$context['message']}\n";
            $body .= "File: {$context['file']} (line {$context['line']})\n";
            $body .= "URL: {$context['request_uri']}\n";
            $body .= "Environment: {$context['environment']}\n";
            $body .= "Time: {$context['timestamp']}\n\n";
            $body .= "Stack Trace:\n{$context['trace']}\n";
            
            // Send email
            mail($to, $subject, $body);
        }
        
        // Other notification methods can be added here
        // as needed for slack, sms, etc.
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
        try {
            $view = config('exceptions.views.not_found', 'errors.404');
            // Pass false as layout to avoid layout rendering (prevents layout not found errors)
            return Response::view($view, ['exception' => $e], false)->setStatus(404);
        } catch (Throwable $viewError) {
            // If no template can be found, throw an exception
            throw new \RuntimeException("404 error template not found. Please make sure template files exist in templates/default/views/errors directory.");
        }
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
            
            try {
                $view = config('exceptions.views.validation', 'errors.validation');
                return Response::view($view, [
                    'errors' => $e->errors()
                ], false)->setStatus(422);
            } catch (Throwable $viewError) {
                // If no template can be found, throw an exception
                throw new \RuntimeException("Validation error template not found. Please make sure template files exist in templates/default/views/errors directory.");
            }
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
        try {
            $view = config('exceptions.views.database', 'errors.database');
            
            if (config('exceptions.debug', false) == true) {
                return Response::view($view, [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTrace()
                ], false)->setStatus(500);
            }
            
            return Response::view($view, [
                'message' => 'A database error has occurred.'
            ], false)->setStatus(500);
        } catch (Throwable $viewError) {
            // If no template can be found, throw an exception
            throw new \RuntimeException("Database error template not found. Please make sure template files exist in templates/default/views/errors directory.");
        }
    }
    
    /**
     * Render a response for generic exceptions
     *
     * @param Throwable $e
     * @return ResponseContract
     */
    protected function renderGenericException(Throwable $e): ResponseContract
    {
        try {
            $view = config('exceptions.views.general', 'errors.application');
            
            if (config('exceptions.debug', false) == true) {
                return Response::view($view, [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTrace()
                ], false)->setStatus(500);
            }
            
            return Response::view($view, [
                'message' => 'An unexpected error occurred.'
            ], false)->setStatus(500);
        } catch (Throwable $viewError) {
            // If no template can be found, throw an exception
            throw new \RuntimeException("Application error template not found. Please make sure template files exist in templates/default/views/errors directory.");
        }
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
    
    /**
     * Fallback logging when logger is not available
     *
     * @param Throwable $e
     * @param string $level
     * @return void
     */
    protected function fallbackLog(Throwable $e, string $level): void
    {
        try {
            $logPath = function_exists('storagePath') 
                ? storagePath('logs/exceptions.log')
                : (dirname(__DIR__, 3) . '/storage/logs/exceptions.log');
                
            $logDir = dirname($logPath);
            
            // Create log directory if it doesn't exist
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            
            $timestamp = date('Y-m-d H:i:s');
            $message = sprintf(
                "[%s] %s: %s in %s on line %d\n%s\n\n",
                $timestamp,
                strtoupper($level),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
                $e->getTraceAsString()
            );
            
            file_put_contents($logPath, $message, FILE_APPEND);
        } catch (\Throwable $fallbackException) {
            // Last resort - use PHP's error_log if everything else fails
            error_log("Exception: {$e->getMessage()} in {$e->getFile()} on line {$e->getLine()}");
        }
    }
}
