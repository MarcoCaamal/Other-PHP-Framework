<?php

namespace LightWeight\Providers;

use DI\Container as DIContainer;
use LightWeight\Log\Contracts\LoggerContract;
use LightWeight\Log\Logger;
use LightWeight\Providers\Contracts\ServiceProviderContract;

/**
 * Log Service Provider
 * 
 * Registers the logger implementation in the container
 */
class LogServiceProvider implements ServiceProviderContract
{
    /**
     * Register the service provider
     * 
     * @param DIContainer $container The container instance
     * @return void
     */
    public function registerServices(DIContainer $container): void
    {
        // Bind the LoggerContract to the Logger implementation
        $container->set(LoggerContract::class, function ($container) {
            $channel = config('logging.default_channel', 'LightWeight');
            $config = config('logging.channels.' . $channel, []);
            
            if (!isset($config['path'])) {
                $config['path'] = storagePath('logs/lightweight.log');
            }
            
            return new Logger($channel, $config);
        });

        // Bind the logger() helper function
        if (!function_exists('logger')) {
            $container->set('logger', function ($container) {
                return $container->get(LoggerContract::class);
            });
        }
    }
}
