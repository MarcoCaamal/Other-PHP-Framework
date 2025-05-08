<?php

namespace LightWeight\Tests\Database\QueryBuilder;

use PHPUnit\Framework\TestCase;
use LightWeight\Tests\Database\RefreshDatabase;
use LightWeight\Database\Contracts\DatabaseDriverContract;
use LightWeight\Database\QueryBuilder\Builder;
use LightWeight\Database\QueryBuilder\Drivers\MySQLQueryBuilder;

class AdvancedQueryBuilderTest extends TestCase
{
    use RefreshDatabase {
        setUp as protected traitSetUp;
        tearDown as protected traitTearDown;
    }

    protected ?DatabaseDriverContract $driver = null;
    protected ?Builder $builder = null;
    
    protected function setUp(): void
    {
        // Call the trait's setup first
        $this->traitSetUp();
        
        // Now create the builder with the driver initialized by RefreshDatabase trait
        if ($this->builder === null) {
            $this->builder = new Builder(new MySQLQueryBuilder($this->driver));
        }
    }
    
    protected function tearDown(): void
    {
        $this->traitTearDown();
    }
    
    private function createTestTable($name, $columns, $withTimestamps = true)
    {
        $sql = "CREATE TABLE $name (id INT AUTO_INCREMENT PRIMARY KEY, "
            . implode(", ", array_map(fn ($c) => "$c VARCHAR(256)", $columns));
        if ($withTimestamps) {
            $sql .= ", created_at DATETIME, updated_at DATETIME NULL";
        }
        $sql .= ")";
        $this->driver->statement($sql);
    }
    
    private function insertTestData($table, $data)
    {
        foreach ($data as $row) {
            $columns = implode(', ', array_keys($row));
            $placeholders = implode(', ', array_fill(0, count($row), '?'));
            $this->driver->statement(
                "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})",
                array_values($row)
            );
        }
    }
    
    public function testAggregates()
    {
        $this->createTestTable('products', ['name', 'price', 'category']);
        $this->insertTestData('products', [
            ['name' => 'Product 1', 'price' => '100', 'category' => 'A'],
            ['name' => 'Product 2', 'price' => '200', 'category' => 'A'],
            ['name' => 'Product 3', 'price' => '300', 'category' => 'B'],
            ['name' => 'Product 4', 'price' => '400', 'category' => 'B'],
            ['name' => 'Product 5', 'price' => '500', 'category' => 'C'],
        ]);
        
        // Test sum
        $sum = $this->builder->table('products')->sum('price');
        $this->assertEquals(1500, $sum);
        
        // Test avg
        $avg = $this->builder->table('products')->avg('price');
        $this->assertEquals(300, $avg);
        
        // Test min
        $min = $this->builder->table('products')->min('price');
        $this->assertEquals(100, $min);
        
        // Test max
        $max = $this->builder->table('products')->max('price');
        $this->assertEquals(500, $max);
        
        // Test aggregate with where condition
        $sumCategory = $this->builder->table('products')
            ->where('category', '=', 'B')
            ->sum('price');
        $this->assertEquals(700, $sumCategory);
    }
    
    public function testGroupByAndHaving()
    {
        $this->createTestTable('sales', ['product', 'category', 'amount']);
        $this->insertTestData('sales', [
            ['product' => 'Product 1', 'category' => 'A', 'amount' => '100'],
            ['product' => 'Product 2', 'category' => 'A', 'amount' => '200'],
            ['product' => 'Product 3', 'category' => 'B', 'amount' => '150'],
            ['product' => 'Product 4', 'category' => 'B', 'amount' => '250'],
            ['product' => 'Product 5', 'category' => 'A', 'amount' => '300'],
        ]);
        
        // Test groupBy
        $results = $this->builder->table('sales')
            ->select(['category', $this->builder->table('sales')->sum('amount')])
            ->groupBy(['category'])
            ->get();
        
        $this->assertCount(2, $results);
        
        // Test having
        $results = $this->builder->table('sales')
            ->select(['category', $this->builder->table('sales')->sum('amount')])
            ->groupBy(['category'])
            ->having('sum(amount)', '>', '400')
            ->get();
        
        $this->assertCount(1, $results);
        $this->assertEquals('A', $results[0]['category']);
    }
    
    public function testWhereNotIn()
    {
        $this->createTestTable('users', ['name', 'role']);
        $this->insertTestData('users', [
            ['name' => 'User 1', 'role' => 'admin'],
            ['name' => 'User 2', 'role' => 'user'],
            ['name' => 'User 3', 'role' => 'editor'],
            ['name' => 'User 4', 'role' => 'user'],
            ['name' => 'User 5', 'role' => 'guest'],
        ]);
        
        $results = $this->builder->table('users')
            ->whereNotIn('role', ['admin', 'guest'])
            ->get();
        
        $this->assertCount(3, $results);
        $this->assertEquals('User 2', $results[0]['name']);
        $this->assertEquals('User 3', $results[1]['name']);
        $this->assertEquals('User 4', $results[2]['name']);
    }
    
    public function testWhereNullAndWhereNotNull()
    {
        $this->createTestTable('contacts', ['name', 'email', 'phone']);
        $this->insertTestData('contacts', [
            ['name' => 'Contact 1', 'email' => 'email1@example.com', 'phone' => '123456789'],
            ['name' => 'Contact 2', 'email' => 'email2@example.com', 'phone' => null],
            ['name' => 'Contact 3', 'email' => null, 'phone' => '987654321'],
            ['name' => 'Contact 4', 'email' => null, 'phone' => null],
        ]);
        
        // Test whereNull
        $results = $this->builder->table('contacts')
            ->whereNull('email')
            ->get();
        
        $this->assertCount(2, $results);
        $this->assertEquals('Contact 3', $results[0]['name']);
        $this->assertEquals('Contact 4', $results[1]['name']);
        
        // Test whereNotNull
        $results = $this->builder->table('contacts')
            ->whereNotNull('email')
            ->get();
        
        $this->assertCount(2, $results);
        $this->assertEquals('Contact 1', $results[0]['name']);
        $this->assertEquals('Contact 2', $results[1]['name']);
        
        // Test combining whereNull conditions
        $results = $this->builder->table('contacts')
            ->whereNull('email')
            ->whereNotNull('phone')
            ->get();
        
        $this->assertCount(1, $results);
        $this->assertEquals('Contact 3', $results[0]['name']);
    }
    
    public function testWhereBetween()
    {
        $this->createTestTable('products', ['name', 'price']);
        $this->insertTestData('products', [
            ['name' => 'Product 1', 'price' => '50'],
            ['name' => 'Product 2', 'price' => '100'],
            ['name' => 'Product 3', 'price' => '150'],
            ['name' => 'Product 4', 'price' => '200'],
            ['name' => 'Product 5', 'price' => '250'],
        ]);
        
        // Test whereBetween
        $results = $this->builder->table('products')
            ->whereBetween('price', '100', '200')
            ->get();
        
        $this->assertCount(3, $results);
        $this->assertEquals('Product 2', $results[0]['name']);
        $this->assertEquals('Product 3', $results[1]['name']);
        $this->assertEquals('Product 4', $results[2]['name']);
        
        // Test whereNotBetween
        $results = $this->builder->table('products')
            ->whereNotBetween('price', '100', '200')
            ->get();
        
        $this->assertCount(2, $results);
        $this->assertEquals('Product 1', $results[0]['name']);
        $this->assertEquals('Product 5', $results[1]['name']);
    }
    
    public function testComplexQueries()
    {
        $this->createTestTable('orders', ['customer', 'product', 'amount', 'status']);
        $this->insertTestData('orders', [
            ['customer' => 'Customer 1', 'product' => 'Product A', 'amount' => '100', 'status' => 'completed'],
            ['customer' => 'Customer 1', 'product' => 'Product B', 'amount' => '200', 'status' => 'pending'],
            ['customer' => 'Customer 2', 'product' => 'Product A', 'amount' => '150', 'status' => 'completed'],
            ['customer' => 'Customer 3', 'product' => 'Product C', 'amount' => '300', 'status' => 'cancelled'],
            ['customer' => 'Customer 2', 'product' => 'Product B', 'amount' => '250', 'status' => 'pending'],
            ['customer' => 'Customer 1', 'product' => 'Product C', 'amount' => '350', 'status' => 'completed'],
        ]);
        
        // Complex query with multiple conditions and ordering
        $results = $this->builder->table('orders')
            ->where('status', '=', 'completed')
            ->where('amount', '>', '120')
            ->orderBy('amount', 'desc')
            ->get();
        
        $this->assertCount(2, $results);
        $this->assertEquals('Customer 1', $results[0]['customer']);
        $this->assertEquals('Product C', $results[0]['product']);
        $this->assertEquals('350', $results[0]['amount']);
        
        // Query with OR conditions
        $results = $this->builder->table('orders')
            ->where('customer', '=', 'Customer 1')
            ->orWhere('customer', '=', 'Customer 3')
            ->get();
        
        $this->assertCount(4, $results);
        
        // Query with subgroup of conditions
        $results = $this->builder->table('orders')
            ->where('status', '=', 'pending')
            ->whereGroup(function($query) {
                $query->where('customer', '=', 'Customer 1')
                      ->orWhere('amount', '>', '200');
            })
            ->get();
        
        $this->assertCount(2, $results);
    }
}
