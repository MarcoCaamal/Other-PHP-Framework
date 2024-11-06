<?php

namespace LightWeight\Providers;

use LightWeight\Database\Contracts\DatabaseDriverContract;
use LightWeight\Database\PdoDriver;
use LightWeight\Database\QueryBuilder\Drivers\MysqlQueryBuilderDriver;
use LightWeight\Providers\Contracts\ServiceProviderContract;

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
