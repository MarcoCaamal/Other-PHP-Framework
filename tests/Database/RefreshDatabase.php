<?php

namespace LightWeight\Tests\Database;

use LightWeight\Container\Container;
use LightWeight\Database\Contracts\DatabaseDriverContract;
use LightWeight\Database\ORM\Model;
use LightWeight\Database\PdoDriver;
use LightWeight\Database\QueryBuilder\Contracts\QueryBuilderContract;
use LightWeight\Database\QueryBuilder\Drivers\MySQLQueryBuilder;
use PDOException;
use Psr\Container\ContainerInterface;

trait RefreshDatabase
{
    protected function setUp(): void
    {
        if (is_null($this->driver)) {
            // Configure driver as singleton
            $this->driver = singleton(DatabaseDriverContract::class, PdoDriver::class);
            
            // Registrar una fábrica para crear instancias de MySQLQueryBuilder
            Container::getInstance()->set(QueryBuilderContract::class, function(ContainerInterface $c) {
                return new MySQLQueryBuilder($c->get(DatabaseDriverContract::class));
            });

            // Los modelos ahora obtienen sus dependencias del contenedor directamente

            try {
                $dbConnection = getenv('DB_CONNECTION') ?: 'mysql';
                $dbHost = getenv('DB_HOST') ?: '127.0.0.1';
                $dbPort = (int)(getenv('DB_PORT') ?: 3306);
                $dbName = getenv('DB_DATABASE') ?: 'lightweight_test';
                $dbUsername = getenv('DB_USERNAME') ?: 'root';
                $dbPassword = getenv('DB_PASSWORD') ?: '';
                
                $this->driver->connect($dbConnection, $dbHost, $dbPort, $dbName, $dbUsername, $dbPassword);
            } catch (PDOException $e) {
                $this->markTestSkipped("Can't connect to test database: {$e->getMessage()}");
            }
        }
    }
    protected function tearDown(): void
    {
        $dbName = getenv('DB_DATABASE') ?: 'lightweight_test';
        $this->driver->statement("DROP DATABASE IF EXISTS `{$dbName}`");
        $this->driver->statement("CREATE DATABASE `{$dbName}`");
    }
}
