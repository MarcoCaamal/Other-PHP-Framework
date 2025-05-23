<?php

namespace LightWeight\Providers;

use LightWeight\Container\Container;
use LightWeight\Database\Contracts\DatabaseDriverContract;
use LightWeight\Database\PdoDriver;
use LightWeight\Database\QueryBuilder\Contracts\QueryBuilderContract;
use LightWeight\Database\QueryBuilder\Drivers\MySQLQueryBuilder;

class DatabaseDriverServiceProvider extends ServiceProvider
{
    /**
     * Proporciona definiciones para el contenedor antes de su compilación
     *
     * @return array
     */
    public function getDefinitions(): array
    {
        return [
            // Registrar el driver de base de datos
            DatabaseDriverContract::class => \DI\factory(function () {
                return match(config('database.connection', 'mysql')) {
                    'mysql' => new PdoDriver(),
                    default => throw new \LightWeight\Database\Exceptions\DatabaseException("Unsupported database connection type")
                };
            }),

            // Registrar el query builder con el driver correspondiente
            QueryBuilderContract::class => \DI\factory(function (DatabaseDriverContract $driver) {
                return match(config('database.connection', 'mysql')) {
                    'mysql' => new MySQLQueryBuilder($driver),
                    default => throw new \LightWeight\Database\Exceptions\DatabaseException("Unsupported database connection type")
                };
            })
        ];
    }

    /**
     * @inheritDoc
     */
    public function registerServices(Container $serviceContainer)
    {
        // Las definiciones ya están configuradas en getDefinitions()
    }
}
