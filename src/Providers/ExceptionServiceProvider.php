<?php

namespace LightWeight\Providers;

use LightWeight\Exceptions\ExceptionHandler;
use LightWeight\Log\Contracts\LoggerContract;
use LightWeight\Log\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Formatter\LineFormatter;

/**
 * Service provider for exception handling and integration with logging
 */
class ExceptionServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider
     * 
     * @return void
     */
    public function register(): void
    {
        // Register a dedicated exception logger
        $this->registerExceptionLogger();
    }
    
    /**
     * Bootstrap the service provider
     * 
     * @return void
     */
    public function boot(): void
    {
        // Configure the exception logger
        $this->configureExceptionLogger();
    }
    
    /**
     * Register the exception logger
     * 
     * @return void
     */
    protected function registerExceptionLogger(): void
    {
        $this->app->singleton('exception.logger', function ($app) {
            $config = $app->get('config');
            $exceptionsConfig = $config->get('exceptions', []);
            
            // Create a specialized logger for exceptions
            $logger = new Logger('exceptions', [
                'path' => storagePath('logs/exceptions.log'),
                'level' => 'debug',
                'days' => $exceptionsConfig['log']['max_files'] ?? 7,
                'bubble' => true,
                'format' => "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
                'date_format' => 'Y-m-d H:i:s',
            ]);
            
            return $logger;
        });
    }
    
    /**
     * Configure the exception logger with additional handlers
     * 
     * @return void
     */
    protected function configureExceptionLogger(): void
    {
        try {
            $logger = $this->app->get('exception.logger');
            
            if (!$logger instanceof LoggerContract) {
                return;
            }
            
            // Add a separate handler for critical exceptions
            $criticalFormatter = new LineFormatter(
                "[%datetime%] CRITICAL: %message% %context%\n",
                'Y-m-d H:i:s',
                true,
                true
            );
            
            $criticalHandler = new StreamHandler(
                storagePath('logs/critical.log'),
                Level::Critical,
                true
            );
            
            $criticalHandler->setFormatter($criticalFormatter);
            $logger->pushHandler($criticalHandler);
            
            // Register exception logger for use in ExceptionHandler
            $mainLogger = $this->app->get(LoggerContract::class);
            
            // Add the exception logger handlers to the main logger
            foreach ($logger->getLogger()->getHandlers() as $handler) {
                $mainLogger->pushHandler($handler);
            }
        } catch (\Throwable $e) {
            // If we can't configure the logger, log to PHP error log as a fallback
            error_log("Failed to configure exception logger: {$e->getMessage()}");
        }
    }
}
