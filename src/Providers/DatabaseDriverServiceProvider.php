<?php

namespace LightWeight\Providers;

use LightWeight\Database\Contracts\DatabaseDriverContract;
use LightWeight\Database\PdoDriver;
use LightWeight\Providers\Contracts\ServiceProviderContract;

class DatabaseDriverServiceProvider implements ServiceProviderContract
{
    /**
     * @inheritDoc
     */
    public function registerServices(\DI\Container $serviceContainer)
    {
        match(config('database.connection', 'mysql')) {
            'mysql' => $serviceContainer->set(DatabaseDriverContract::class, \DI\create(PdoDriver::class))
        };
    }
}
