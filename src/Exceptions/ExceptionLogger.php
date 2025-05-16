<?php

namespace LightWeight\Exceptions;

use Throwable;

/**
 * Simple logger for exceptions
 */
class ExceptionLogger
{
    /**
     * Log levels
     */
    public const DEBUG = 'DEBUG';
    public const INFO = 'INFO';
    public const NOTICE = 'NOTICE';
    public const WARNING = 'WARNING';
    public const ERROR = 'ERROR';
    public const CRITICAL = 'CRITICAL';
    public const ALERT = 'ALERT';
    public const EMERGENCY = 'EMERGENCY';
    
    /**
     * Log an exception
     *
     * @param Throwable $e The exception to log
     * @param string $level The log level
     * @return void
     */
    public static function log(Throwable $e, string $level = self::ERROR): void
    {
        $logPath = self::getLogPath();
        $logDir = dirname($logPath);
        
        // Create log directory if it doesn't exist
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $message = sprintf(
            "[%s] %s: %s in %s on line %d\n%s\n\n",
            $timestamp,
            $level,
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );
        
        file_put_contents($logPath, $message, FILE_APPEND);
    }
    
    /**
     * Get the log file path
     *
     * @return string
     */
    protected static function getLogPath(): string
    {
        $useDaily = config('exceptions.log.daily', true);
        $basePath = storagePath('logs');
        
        if ($useDaily) {
            return $basePath . '/exceptions-' . date('Y-m-d') . '.log';
        }
        
        return $basePath . '/exceptions.log';
    }
    
    /**
     * Rotate logs if needed
     * 
     * @return void
     */
    public static function rotateLogs(): void
    {
        $maxFiles = config('exceptions.log.max_files', 30);
        $logDir = storagePath('logs');
        
        if (!is_dir($logDir)) {
            return;
        }
        
        $files = glob($logDir . '/exceptions-*.log');
        if (count($files) <= $maxFiles) {
            return;
        }
        
        // Sort files by date (oldest first)
        usort($files, function($a, $b) {
            return filemtime($a) - filemtime($b);
        });
        
        // Delete oldest files
        $filesToDelete = array_slice($files, 0, count($files) - $maxFiles);
        foreach ($filesToDelete as $file) {
            @unlink($file);
        }
    }
}
