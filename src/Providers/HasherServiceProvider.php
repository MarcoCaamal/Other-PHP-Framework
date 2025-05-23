<?php

namespace LightWeight\Providers;

use LightWeight\Container\Container;
use LightWeight\Crypto\Bcrypt;
use LightWeight\Crypto\Contracts\HasherContract;

class HasherServiceProvider extends ServiceProvider
{
    /**
     * Proporciona definiciones para el contenedor antes de su compilación
     *
     * @return array
     */
    public function getDefinitions(): array
    {
        return [
            HasherContract::class => \DI\factory(function () {
                return match(config('hashing.hasher', 'bcrypt')) {
                    'bcrypt' => new Bcrypt(),
                    default => throw new \RuntimeException("Hasher not supported: " . config('hashing.hasher'))
                };
            })
        ];
    }

    public function registerServices(Container $serviceContainer)
    {
        // Las definiciones ya están configuradas en getDefinitions()
    }
}
