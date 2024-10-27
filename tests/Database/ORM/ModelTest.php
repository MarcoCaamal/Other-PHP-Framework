<?php

namespace SMFramework\Tests\Database\ORM;

use PDOException;
use PHPUnit\Framework\TestCase;
use SMFramework\Database\DatabaseDriverContract;
use SMFramework\Database\ORM\Model;
use SMFramework\Database\PdoDriver;

class MockModel extends Model
{
    //
}

class ModelTest extends TestCase
{
    protected ?DatabaseDriverContract $driver = null;
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
    private function createTestTable($name, $columns, $withTimestamps = false)
    {
        $sql = "CREATE TABLE $name (id INT AUTO_INCREMENT PRIMARY KEY, "
            . implode(", ", array_map(fn ($c) => "$c VARCHAR(256)", $columns));
        if ($withTimestamps) {
            $sql .= ", created_at DATETIME, updated_at DATETIME NULL";
        }
        $sql .= ")";
        $this->driver->statement($sql);
    }
    public function testSaveBasicModelWithAttributes()
    {
        $this->createTestTable("mock_models", ["test", "name"], true);
        $model = new MockModel();
        $model->test = "Test";
        $model->name = "Name";
        $model->save();
        $rows = $this->driver->statement("SELECT * FROM mock_models");
        $expected = [
            "id" => 1,
            "name" => "Name",
            "test" => "Test",
            "created_at" => date("Y-m-d H:m:s"),
            "updated_at" => null,
        ];
        $this->assertEquals($expected, $rows[0]);
        $this->assertEquals(1, count($rows));
    }
}
