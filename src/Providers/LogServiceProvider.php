<?php

namespace LightWeight\Providers;

use LightWeight\Container\Container;
use LightWeight\Log\Contracts\LoggerContract;
use LightWeight\Log\Logger;

/**
 * Log Service Provider
 * 
 * Registers the logger implementation in the container
 */
class LogServiceProvider extends ServiceProvider
{
    /**
     * Proporciona definiciones para el contenedor antes de su compilación
     * 
     * @return array
     */
    public function getDefinitions(): array
    {
        return [
            LoggerContract::class => \DI\factory(function () {
                $channel = config('logging.default_channel', 'LightWeight');
                $config = config('logging.channels.' . $channel, []);
                
                if (!isset($config['path'])) {
                    $config['path'] = storagePath('logs/lightweight.log');
                }
                
                return new Logger($channel, $config);
            }),
            'logger' => \DI\get(LoggerContract::class)
        ];
    }
    
    /**
     * Register the service provider
     * 
     * @param Container $container The container instance
     * @return void
     */
    public function registerServices(Container $container): void
    {
        // Las definiciones ya están configuradas en getDefinitions()
    }
}
