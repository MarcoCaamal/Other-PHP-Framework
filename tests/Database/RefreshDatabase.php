<?php

namespace LightWeight\Tests\Database;

use PDOException;
use LightWeight\Database\Contracts\DatabaseDriverContract;
use LightWeight\Database\ORM\Model;
use LightWeight\Database\PdoDriver;

trait RefreshDatabase
{
    protected function setUp(): void
    {
        if (is_null($this->driver)) {
            $this->driver = singleton(DatabaseDriverContract::class, PdoDriver::class);
            Model::setDatabaseDriver($this->driver);
            try {
                $this->driver->connect('mysql', 'localhost', 3306, 'LightWeight_test', 'root', '');
            } catch (PDOException $e) {
                $this->markTestSkipped("Can't connect to test database: {$e->getMessage()}");
            }
        }
    }
    protected function tearDown(): void
    {
        $this->driver->statement("DROP DATABASE IF EXISTS LightWeight_test");
        $this->driver->statement("CREATE DATABASE LightWeight_test");
    }
}
