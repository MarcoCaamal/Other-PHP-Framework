<?php

namespace SMFramework\Providers;

use SMFramework\Database\Contracts\DatabaseDriverContract;
use SMFramework\Database\PdoDriver;
use SMFramework\Providers\Contracts\ServiceProviderContract;

class DatabaseDriverServiceProvider implements ServiceProviderContract
{
    /**
     * @inheritDoc
     */
    public function registerServices()
    {
        match(config('database.connection', 'mysql')) {
            'mysql' => singleton(DatabaseDriverContract::class, PdoDriver::class)
        };
    }
}
