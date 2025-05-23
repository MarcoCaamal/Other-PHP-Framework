<?php

namespace LightWeight\Providers;

use LightWeight\Container\Container;
use LightWeight\Exceptions\Contracts\ExceptionHandlerContract;

/**
 * Service provider for exception handling
 */
class ExceptionHandlerServiceProvider extends ServiceProvider
{
    /**
     * Proporciona definiciones para el contenedor antes de su compilación
     *
     * @return array
     */
    public function getDefinitions(): array
    {

        return [
            ExceptionHandlerContract::class => \DI\factory(function (\LightWeight\Config\Config $config) {
                $exceptionHandler = $config->get('exceptions.handler', null);
                if ($exceptionHandler === null) {
                    throw new \RuntimeException("Exception handler not configured");
                }
                return new $exceptionHandler();
            }),
        ];
    }

    /**
     * Register the exception handler service
     *
     * @param Container $container
     * @return void
     */
    public function registerServices(Container $container): void
    {
        // Las definiciones ya están configuradas en getDefinitions()
    }
}
