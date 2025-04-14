<?php

namespace LightWeight\Tests\Database;

use LightWeight\Database\Contracts\DatabaseDriverContract;
use LightWeight\Database\ORM\Model;
use LightWeight\Database\PdoDriver;
use LightWeight\Database\QueryBuilder\Drivers\MySQLQueryBuilder;
use PDOException;

trait RefreshDatabase
{
    protected function setUp(): void
    {
        if (is_null($this->driver)) {
            $this->driver = singleton(DatabaseDriverContract::class, PdoDriver::class);

            Model::setDatabaseDriver($this->driver);
            Model::setBuilderDriver(new MySQLQueryBuilder($this->driver));

            try {
                $this->driver->connect('mysql', 'localhost', 3306, 'lightweight_test', 'root', '');
            } catch (PDOException $e) {
                $this->markTestSkipped("Can't connect to test database: {$e->getMessage()}");
            }
        }
    }
    protected function tearDown(): void
    {
        $this->driver->statement("DROP DATABASE IF EXISTS lightweight_test");
        $this->driver->statement("CREATE DATABASE lightweight_test");
    }
}
