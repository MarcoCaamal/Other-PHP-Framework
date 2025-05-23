<?php

namespace LightWeight\Providers;

use LightWeight\Container\Container;
use LightWeight\Session\Contracts\SessionStorageContract;
use LightWeight\Session\PhpNativeSessionStorage;

class SessionStorageServiceProvider extends ServiceProvider
{
    /**
     * Proporciona definiciones para el contenedor antes de su compilación
     *
     * @return array
     */
    public function getDefinitions(): array
    {
        return [
            SessionStorageContract::class => \DI\factory(function () {
                return match(config('session.storage', 'native')) {
                    'native' => new PhpNativeSessionStorage(),
                    default => throw new \RuntimeException("Session storage not supported: " . config('session.storage'))
                };
            })
        ];
    }

    public function registerServices(Container $serviceContainer)
    {
        // La configuración ya está definida en getDefinitions()
    }
}
