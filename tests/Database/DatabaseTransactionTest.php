<?php

namespace LightWeight\Tests\Database;

use LightWeight\Container\Container;
use LightWeight\Events\Contracts\EventDispatcherContract;
use LightWeight\Events\EventDispatcher;
use PHPUnit\Framework\TestCase;
use LightWeight\Tests\Database\RefreshDatabase;
use LightWeight\Database\Contracts\DatabaseDriverContract;
use LightWeight\Database\DB;
use Exception;

class TransactionModel extends \LightWeight\Database\ORM\Model
{
    protected ?string $table = "transaction_models";
    protected array $fillable = ['name', 'value', 'created_at'];
}

class DatabaseTransactionTest extends TestCase
{
    use RefreshDatabase {
        setUp as refreshDatabaseSetUp;
        tearDown as refreshDatabaseTearDown;
    }
    protected function setUp(): void
    {
        $this->refreshDatabaseSetUp();
        singleton(EventDispatcherContract::class, EventDispatcher::class);
    }
    protected function tearDown(): void
    {
        $this->refreshDatabaseTearDown();
        Container::deleteInstance();
    }

    protected ?DatabaseDriverContract $driver = null;

    private function createTransactionTable()
    {
        $this->driver->statement("
            CREATE TABLE transaction_models (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(256),
                value VARCHAR(256),
                created_at DATETIME,
                updated_at DATETIME NULL
            )ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function testSuccessfulTransaction()
    {
        $this->createTransactionTable();
        
        // Start a transaction
        $result = $this->driver->beginTransaction();
        $this->assertTrue($result);
        
        try {
            // Perform operations
            TransactionModel::create([
                'name' => 'Transaction Test 1',
                'value' => 'Value 1'
            ]);
            
            TransactionModel::create([
                'name' => 'Transaction Test 2',
                'value' => 'Value 2'
            ]);
            
            // Commit the transaction
            $this->driver->commit();
            
            // Verify both records were saved
            $count = TransactionModel::query()->count();
            $this->assertEquals(2, $count);
        } catch (Exception $e) {
            var_dump($e->getMessage());
            $this->driver->rollback();
            $this->fail('Transaction should not have thrown an exception');
        }
    }

    public function testRollbackTransaction()
    {
        $this->createTransactionTable();
        
        // Create one record outside transaction using direct DB query for reliability
        $this->driver->statement(
            "INSERT INTO transaction_models (name, value, created_at) VALUES (?, ?, ?)",
            ['Outside Transaction', 'Safe', date("Y-m-d H:i:s")]
        );
        
        // Start a transaction
        $result = $this->driver->beginTransaction();
        $this->assertTrue($result);
        
        try {
            // Perform operations within transaction
            $this->driver->statement(
                "INSERT INTO transaction_models (name, value, created_at) VALUES (?, ?, ?)",
                ['Will be rolled back', 'Not Safe', date("Y-m-d H:i:s")]
            );
            
            // Simulate an error condition
            throw new Exception('Simulated error');
            
            // This line should not execute
            $this->driver->commit();
        } catch (Exception $e) {
            // Rollback the transaction
            $this->driver->rollback();
        }
        
        // Get raw data from database to verify the transaction rollback worked
        $rawRows = $this->driver->statement("SELECT * FROM transaction_models");
        $this->assertCount(1, $rawRows);
        $this->assertEquals('Outside Transaction', $rawRows[0]['name']);
        
        // Also verify using the ORM
        $models = TransactionModel::all();
        $this->assertCount(1, $models);
        $this->assertEquals('Outside Transaction', $models[0]->name);
    }

    public function testDbHelperWithTransaction()
    {
        $this->createTransactionTable();
        
        // Test using the DB helper class for transactions
        try {
            DB::beginTransaction();
            
            // Create some records
            DB::table('transaction_models')->insert([
                'name' => 'DB Helper Test 1',
                'value' => 'From DB Helper',
                'created_at' => date("Y-m-d H:i:s")
            ]);
            
            DB::table('transaction_models')->insert([
                'name' => 'DB Helper Test 2',
                'value' => 'Also From DB Helper',
                'created_at' => date("Y-m-d H:i:s")
            ]);
            
            DB::commit();
            
            // Verify records were created
            $count = DB::table('transaction_models')->count();
            $this->assertEquals(2, $count);
        } catch (Exception $e) {
            DB::rollback();
            $this->fail('DB Helper transaction should not have thrown an exception: ' . $e->getMessage());
        }
    }

    public function testTransactionRollbackWithDbHelper()
    {
        $this->createTransactionTable();
        
        // Create one record outside transaction for reference
        DB::table('transaction_models')->insert([
            'name' => 'Outside Transaction',
            'value' => 'Safe',
            'created_at' => date("Y-m-d H:i:s")
        ]);
        
        try {
            DB::beginTransaction();
            
            DB::table('transaction_models')->insert([
                'name' => 'Will be rolled back',
                'value' => 'Not Safe',
                'created_at' => date("Y-m-d H:i:s")
            ]);
            
            // Simulate an error
            throw new Exception('Simulated error with DB Helper');
            
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
        }
        
        // Verify only the first record exists
        $count = DB::table('transaction_models')->count();
        $this->assertEquals(1, $count);
    }
}
