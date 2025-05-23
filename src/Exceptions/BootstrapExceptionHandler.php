<?php

namespace LightWeight\Exceptions;

use LightWeight\Application;
use LightWeight\Exceptions\Contracts\BootstrapExceptionHandlerContract;
use LightWeight\Exceptions\LightWeightException;
use Throwable;

/**
 * Handles exceptions that occur during the application bootstrap process
 */
class BootstrapExceptionHandler implements BootstrapExceptionHandlerContract
{
    /**
     * Log the bootstrap exception
     *
     * @param Throwable $exception The exception to log
     * @return void
     */
    public function logException(Throwable $exception): void
    {
        // Check if App::$root is defined and accessible
        $logPath = class_exists('LightWeight\Application') && isset(Application::$root)
            ? Application::$root . '/storage/logs/bootstrap-errors.log'
            : __DIR__ . '/../../storage/logs/bootstrap-errors.log';

        // Make sure the log directory exists
        $logDir = dirname($logPath);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        // Format the exception message
        $message = sprintf(
            "[%s] %s: %s in %s on line %d\n%s\n\n",
            date('Y-m-d H:i:s'),
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );

        // Write to log file
        file_put_contents($logPath, $message, FILE_APPEND);
    }

    /**
     * Handle a bootstrap exception
     *
     * @param Throwable $exception The exception to handle
     * @return void
     */
    public function handleException(Throwable $exception): void
    {
        // Log the exception
        $this->logException($exception);

        // Display an appropriate error message
        if (php_sapi_name() === 'cli') {
            $this->renderCliError($exception);
        } else {
            $this->renderHttpError($exception);
        }

        // End execution with an error code
        exit(1);
    }

    /**
     * Render an error message for CLI environment
     *
     * @param Throwable $exception The exception to render
     * @return void
     */
    protected function renderCliError(Throwable $exception): void
    {
        $message = sprintf(
            "\n\033[31mBootstrap Error: %s\033[0m\n%s in %s on line %d\n\n",
            $exception->getMessage(),
            get_class($exception),
            $exception->getFile(),
            $exception->getLine()
        );

        fwrite(STDERR, $message);
    }

    /**
     * Render an error message for HTTP environment
     *
     * @param Throwable $exception The exception to render
     * @return void
     */
    protected function renderHttpError(Throwable $exception): void
    {
        http_response_code(500);

        // Check if APP_DEBUG is set to true (as a string or boolean)
        $isDebug = env('APP_DEBUG', false);

        if ($isDebug) {
            echo $this->getDebugErrorPage($exception);
        } else {
            echo $this->getProductionErrorPage();
        }
    }

    /**
     * Get a debug error page with detailed exception information
     *
     * @param Throwable $exception The exception to display
     * @return string HTML content for the error page
     */
    protected function getDebugErrorPage(Throwable $exception): string
    {
        $exceptionClass = htmlspecialchars(get_class($exception));
        $message = htmlspecialchars($exception->getMessage());
        $file = htmlspecialchars($exception->getFile());
        $line = $exception->getLine();
        $trace = nl2br(htmlspecialchars($exception->getTraceAsString()));

        $html = '<!DOCTYPE html>';
        $html .= '<html>';
        $html .= '<head>';
        $html .= '    <meta charset="UTF-8">';
        $html .= '    <meta name="viewport" content="width=device-width, initial-scale=1.0">';
        $html .= '    <title>Bootstrap Error</title>';
        $html .= '    <style>';
        $html .= '        body { font-family: sans-serif; line-height: 1.5; padding: 2rem; color: #333; max-width: 1200px; margin: 0 auto; }';
        $html .= '        .error-container { background-color: #f8d7da; border-radius: 5px; padding: 1rem 2rem; margin-bottom: 2rem; border-left: 5px solid #dc3545; }';
        $html .= '        .error-title { color: #dc3545; margin-top: 0; }';
        $html .= '        .error-details { background-color: #f8f9fa; padding: 1rem; border-radius: 5px; overflow: auto; }';
        $html .= '        .error-location { font-family: monospace; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #dee2e6; }';
        $html .= '        .error-stack { font-family: monospace; white-space: pre-wrap; font-size: 0.9rem; }';
        $html .= '        code { background: #f0f0f0; padding: 0.2rem 0.4rem; border-radius: 3px; font-size: 0.9em; }';
        $html .= '    </style>';
        $html .= '</head>';
        $html .= '<body>';
        $html .= '    <div class="error-container">';
        $html .= '        <h1 class="error-title">Bootstrap Error</h1>';
        $html .= '        <p>The application failed to start properly due to an error.</p>';
        $html .= '    </div>';
        $html .= '    <div class="error-details">';
        $html .= '        <h2>Exception Details</h2>';
        $html .= '        <div class="error-location">';
        $html .= '            <strong>Type:</strong> ' . $exceptionClass . '<br>';
        $html .= '            <strong>Message:</strong> ' . $message . '<br>';
        $html .= '            <strong>File:</strong> ' . $file . '<br>';
        $html .= '            <strong>Line:</strong> ' . $line;
        $html .= '        </div>';
        $html .= '        <h3>Stack Trace</h3>';
        $html .= '        <div class="error-stack">' . $trace . '</div>';
        $html .= '    </div>';
        $html .= '    <p>This detailed error is shown because <code>APP_DEBUG</code> is enabled. Set it to <code>false</code> in production.</p>';
        $html .= '</body>';
        $html .= '</html>';

        return $html;
    }

    /**
     * Get a production error page with minimal information
     *
     * @return string HTML content for the error page
     */
    protected function getProductionErrorPage(): string
    {
        $html = '<!DOCTYPE html>';
        $html .= '<html>';
        $html .= '<head>';
        $html .= '    <meta charset="UTF-8">';
        $html .= '    <meta name="viewport" content="width=device-width, initial-scale=1.0">';
        $html .= '    <title>Server Error</title>';
        $html .= '    <style>';
        $html .= '        body { font-family: sans-serif; line-height: 1.5; padding: 2rem; color: #333; text-align: center; max-width: 800px; margin: 0 auto; }';
        $html .= '        .error-container { background-color: #f8d7da; border-radius: 5px; padding: 2rem; margin: 3rem auto; border: 1px solid #f5c6cb; }';
        $html .= '        .error-title { color: #721c24; margin-top: 0; }';
        $html .= '    </style>';
        $html .= '</head>';
        $html .= '<body>';
        $html .= '    <div class="error-container">';
        $html .= '        <h1 class="error-title">Server Error</h1>';
        $html .= '        <p>The application encountered an error during startup. Please try again later.</p>';
        $html .= '        <p>If the problem persists, please contact the system administrator.</p>';
        $html .= '    </div>';
        $html .= '</body>';
        $html .= '</html>';

        return $html;
    }
}
