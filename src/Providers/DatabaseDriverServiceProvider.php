<?php

namespace LightWeight\Providers;

use LightWeight\Database\Contracts\DatabaseDriverContract;
use LightWeight\Database\PdoDriver;
use LightWeight\Database\QueryBuilder\Contracts\QueryBuilderContract;
use LightWeight\Database\QueryBuilder\Drivers\MySQLQueryBuilder;
use LightWeight\Database\ORM\Model;
use LightWeight\Providers\Contracts\ServiceProviderContract;

class DatabaseDriverServiceProvider implements ServiceProviderContract
{
    /**
     * @inheritDoc
     */
    public function registerServices(\DI\Container $serviceContainer)
    {
        // Registrar el driver de base de datos
        match(config('database.connection', 'mysql')) {
            'mysql' => $serviceContainer->set(DatabaseDriverContract::class, \DI\create(PdoDriver::class))
        };
        
        // Registrar el query builder con el driver correspondiente
        $serviceContainer->set(QueryBuilderContract::class, function() use ($serviceContainer) {
            $driver = $serviceContainer->get(DatabaseDriverContract::class);
            $queryBuilder = match(config('database.connection', 'mysql')) {
                'mysql' => new MySQLQueryBuilder($driver)
            };
            
            return $queryBuilder;
        });
        
        // Configurar el Model para usar el driver y query builder registrados
        $serviceContainer->call(function(DatabaseDriverContract $databaseDriver, QueryBuilderContract $queryBuilder) {
            Model::setDatabaseDriver($databaseDriver);
            Model::setBuilderDriver($queryBuilder);
        });
    }
}
