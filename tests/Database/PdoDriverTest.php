<?php

namespace LightWeight\Tests\Database;

use PHPUnit\Framework\TestCase;
use LightWeight\Database\PdoDriver;
use LightWeight\Database\Contracts\DatabaseDriverContract;
use PDOException;

class PdoDriverTest extends TestCase
{
    private ?PdoDriver $driver = null;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->driver = new PdoDriver();
        
        try {
            $dbConnection = getenv('DB_CONNECTION') ?: 'mysql';
            $dbHost = getenv('DB_HOST') ?: '127.0.0.1';
            $dbPort = (int)(getenv('DB_PORT') ?: 3306);
            $dbName = getenv('DB_DATABASE') ?: 'lightweight_test';
            $dbUsername = getenv('DB_USERNAME') ?: 'root';
            $dbPassword = getenv('DB_PASSWORD') ?: '';
            
            $this->driver->connect($dbConnection, $dbHost, $dbPort, $dbName, $dbUsername, $dbPassword);
        } catch (PDOException $e) {
            $this->markTestSkipped("Cannot connect to database: " . $e->getMessage());
        }
        
        // Clean up database
        $this->driver->statement("DROP TABLE IF EXISTS pdo_test");
        $this->driver->statement("CREATE TABLE pdo_test (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255),
            value VARCHAR(255)
        )");
    }
    
    protected function tearDown(): void
    {
        if ($this->driver) {
            $this->driver->statement("DROP TABLE IF EXISTS pdo_test");
            $this->driver->close();
            $this->driver = null;
        }
    }
    
    public function testInstanceOf()
    {
        $this->assertInstanceOf(DatabaseDriverContract::class, $this->driver);
    }
    
    public function testStatement()
    {
        // Insert test data
        $this->driver->statement(
            "INSERT INTO pdo_test (name, value) VALUES (?, ?)",
            ['test1', 'value1']
        );
        $this->driver->statement(
            "INSERT INTO pdo_test (name, value) VALUES (?, ?)",
            ['test2', 'value2']
        );
        
        // Test SELECT statement
        $results = $this->driver->statement("SELECT * FROM pdo_test ORDER BY id");
        
        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertEquals('test1', $results[0]['name']);
        $this->assertEquals('value1', $results[0]['value']);
        $this->assertEquals('test2', $results[1]['name']);
    }
    
    public function testExecute()
    {
        // Test INSERT with execute
        $result = $this->driver->execute(
            "INSERT INTO pdo_test (name, value) VALUES (?, ?)",
            ['executeTest', 'executeValue']
        );
        
        $this->assertTrue($result);
        
        // Verify the insert worked
        $results = $this->driver->statement("SELECT * FROM pdo_test WHERE name = ?", ['executeTest']);
        $this->assertCount(1, $results);
        $this->assertEquals('executeValue', $results[0]['value']);
        
        // Test UPDATE with execute
        $result = $this->driver->execute(
            "UPDATE pdo_test SET value = ? WHERE name = ?",
            ['updatedValue', 'executeTest']
        );
        
        $this->assertTrue($result);
        
        // Verify the update worked
        $results = $this->driver->statement("SELECT * FROM pdo_test WHERE name = ?", ['executeTest']);
        $this->assertEquals('updatedValue', $results[0]['value']);
        
        // Test DELETE with execute
        $result = $this->driver->execute(
            "DELETE FROM pdo_test WHERE name = ?",
            ['executeTest']
        );
        
        $this->assertTrue($result);
        
        // Verify the delete worked
        $results = $this->driver->statement("SELECT * FROM pdo_test WHERE name = ?", ['executeTest']);
        $this->assertCount(0, $results);
    }
    
    public function testLastInsertId()
    {
        $this->driver->statement(
            "INSERT INTO pdo_test (name, value) VALUES (?, ?)",
            ['insertIdTest', 'value']
        );
        
        $insertId = $this->driver->lastInsertId();
        
        $this->assertNotNull($insertId);
        $this->assertIsString($insertId);
        $this->assertGreaterThan(0, (int)$insertId);
        
        // Verify we can find the record with this ID
        $results = $this->driver->statement("SELECT * FROM pdo_test WHERE id = ?", [(int)$insertId]);
        $this->assertCount(1, $results);
        $this->assertEquals('insertIdTest', $results[0]['name']);
    }
    
    public function testTransactions()
    {
        // Start a transaction
        $this->assertTrue($this->driver->beginTransaction());
        
        // Insert within transaction
        $this->driver->statement(
            "INSERT INTO pdo_test (name, value) VALUES (?, ?)",
            ['transaction1', 'value1']
        );
        
        $this->driver->statement(
            "INSERT INTO pdo_test (name, value) VALUES (?, ?)",
            ['transaction2', 'value2']
        );
        
        // Commit the transaction
        $this->assertTrue($this->driver->commit());
        
        // Check that both records exist
        $results = $this->driver->statement("SELECT * FROM pdo_test ORDER BY id");
        $this->assertCount(2, $results);
        
        // Test rollback
        $this->assertTrue($this->driver->beginTransaction());
        
        $this->driver->statement(
            "INSERT INTO pdo_test (name, value) VALUES (?, ?)",
            ['rollback', 'value']
        );
        
        // Should have 3 records temporarily
        $tempResults = $this->driver->statement("SELECT * FROM pdo_test");
        $this->assertCount(3, $tempResults);
        
        // Rollback the transaction
        $this->assertTrue($this->driver->rollback());
        
        // Should be back to 2 records
        $finalResults = $this->driver->statement("SELECT * FROM pdo_test");
        $this->assertCount(2, $finalResults);
    }
}
