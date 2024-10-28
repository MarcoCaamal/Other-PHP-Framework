<?php

namespace SMFramework\Tests\Database;

use PDOException;
use SMFramework\Database\ORM\Model;
use SMFramework\Database\PdoDriver;

trait RefreshDatabase
{
    protected function setUp(): void
    {
        if (is_null($this->driver)) {
            $this->driver = new PdoDriver();
            Model::setDatabaseDriver($this->driver);
            try {
                $this->driver->connect('mysql', 'localhost', 3306, 'smframework_test', 'root', '');
            } catch (PDOException $e) {
                $this->markTestSkipped("Can't connect to test database: {$e->getMessage()}");
            }
        }
    }
    protected function tearDown(): void
    {
        $this->driver->statement("DROP DATABASE IF EXISTS smframework_test");
        $this->driver->statement("CREATE DATABASE smframework_test");
    }
}
