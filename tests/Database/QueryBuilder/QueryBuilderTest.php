<?php

namespace LightWeight\Tests\Database\QueryBuilder;

use PHPUnit\Framework\TestCase;
use LightWeight\Tests\Database\RefreshDatabase;
use LightWeight\Database\QueryBuilder\Builder;
use LightWeight\Database\QueryBuilder\Drivers\MySQLQueryBuilder;
use LightWeight\Database\Contracts\DatabaseDriverContract;

class QueryBuilderTest extends TestCase
{
    use RefreshDatabase {
        setUp as protected traitSetUp;
        tearDown as protected traitTearDown;
    }

    protected ?DatabaseDriverContract $driver = null;
    protected ?Builder $builder = null;
    
    protected function setUp(): void
    {
        $this->traitSetUp();
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
    
    public function testSelect()
    {
        $this->createTestTable('test_table', ['name', 'value']);
        $this->insertTestData('test_table', [
            ['name' => 'test1', 'value' => 'value1'],
            ['name' => 'test2', 'value' => 'value2'],
        ]);
        
        $results = $this->builder->table('test_table')->select(['name', 'value'])->get();
        
        $this->assertCount(2, $results);
        $this->assertEquals('test1', $results[0]['name']);
        $this->assertEquals('value1', $results[0]['value']);
        $this->assertEquals('test2', $results[1]['name']);
        $this->assertEquals('value2', $results[1]['value']);
    }
    
    public function testWhere()
    {
        $this->createTestTable('test_table', ['name', 'value']);
        $this->insertTestData('test_table', [
            ['name' => 'test1', 'value' => 'value1'],
            ['name' => 'test2', 'value' => 'value2'],
            ['name' => 'test3', 'value' => 'value2'],
        ]);
        
        $results = $this->builder->table('test_table')->where('value', '=', 'value2')->get();
        
        $this->assertCount(2, $results);
        $this->assertEquals('test2', $results[0]['name']);
        $this->assertEquals('test3', $results[1]['name']);
    }
    
    public function testOrWhere()
    {
        $this->createTestTable('test_table', ['name', 'value']);
        $this->insertTestData('test_table', [
            ['name' => 'test1', 'value' => 'value1'],
            ['name' => 'test2', 'value' => 'value2'],
            ['name' => 'test3', 'value' => 'value3'],
        ]);
        
        $results = $this->builder->table('test_table')
            ->where('value', '=', 'value1')
            ->orWhere('value', '=', 'value3')
            ->get();
        
        $this->assertCount(2, $results);
        $this->assertEquals('test1', $results[0]['name']);
        $this->assertEquals('test3', $results[1]['name']);
    }
    
    public function testWhereIn()
    {
        $this->createTestTable('test_table', ['name', 'value']);
        $this->insertTestData('test_table', [
            ['name' => 'test1', 'value' => 'value1'],
            ['name' => 'test2', 'value' => 'value2'],
            ['name' => 'test3', 'value' => 'value3'],
        ]);
        
        $results = $this->builder->table('test_table')
            ->whereIn('value', ['value1', 'value3'])
            ->get();
        
        $this->assertCount(2, $results);
        $this->assertEquals('test1', $results[0]['name']);
        $this->assertEquals('test3', $results[1]['name']);
    }
    
    public function testOrderBy()
    {
        $this->createTestTable('test_table', ['name', 'value']);
        $this->insertTestData('test_table', [
            ['name' => 'test3', 'value' => 'value3'],
            ['name' => 'test1', 'value' => 'value1'],
            ['name' => 'test2', 'value' => 'value2'],
        ]);
        
        $results = $this->builder->table('test_table')
            ->orderBy('name', 'asc')
            ->get();
        
        $this->assertCount(3, $results);
        $this->assertEquals('test1', $results[0]['name']);
        $this->assertEquals('test2', $results[1]['name']);
        $this->assertEquals('test3', $results[2]['name']);
    }
    
    public function testLimit()
    {
        $this->createTestTable('test_table', ['name', 'value']);
        $this->insertTestData('test_table', [
            ['name' => 'test1', 'value' => 'value1'],
            ['name' => 'test2', 'value' => 'value2'],
            ['name' => 'test3', 'value' => 'value3'],
        ]);
        
        $results = $this->builder->table('test_table')
            ->limit(2)
            ->get();
        
        $this->assertCount(2, $results);
    }
    
    public function testInsert()
    {
        $this->createTestTable('test_table', ['name', 'value']);
        
        $this->builder->table('test_table')
            ->insert(['name' => 'test1', 'value' => 'value1']);
        
        $results = $this->driver->statement('SELECT * FROM test_table');
        
        $this->assertCount(1, $results);
        $this->assertEquals('test1', $results[0]['name']);
        $this->assertEquals('value1', $results[0]['value']);
    }
    
    public function testUpdate()
    {
        $this->createTestTable('test_table', ['name', 'value']);
        $this->insertTestData('test_table', [
            ['name' => 'test1', 'value' => 'value1'],
            ['name' => 'test2', 'value' => 'value2'],
        ]);
        
        $this->builder->table('test_table')
            ->where('name', '=', 'test1')
            ->update(['value' => 'updated']);
        
        $results = $this->driver->statement('SELECT * FROM test_table WHERE name = ?', ['test1']);
        
        $this->assertCount(1, $results);
        $this->assertEquals('updated', $results[0]['value']);
    }
    
    public function testDelete()
    {
        $this->createTestTable('test_table', ['name', 'value']);
        $this->insertTestData('test_table', [
            ['name' => 'test1', 'value' => 'value1'],
            ['name' => 'test2', 'value' => 'value2'],
        ]);
        
        $this->builder->table('test_table')
            ->where('name', '=', 'test1')
            ->delete();
        
        $results = $this->driver->statement('SELECT * FROM test_table');
        
        $this->assertCount(1, $results);
        $this->assertEquals('test2', $results[0]['name']);
    }
    
    public function testJoin()
    {
        $this->createTestTable('users', ['name'], false);
        $this->createTestTable('posts', ['user_id', 'title'], false);
        
        $this->insertTestData('users', [
            ['id' => 1, 'name' => 'User 1'],
            ['id' => 2, 'name' => 'User 2'],
        ]);
        
        $this->insertTestData('posts', [
            ['user_id' => 1, 'title' => 'Post by User 1'],
            ['user_id' => 2, 'title' => 'Post by User 2'],
            ['user_id' => 1, 'title' => 'Another Post by User 1'],
        ]);
        
        $results = $this->builder->table('users')
            ->select(['users.name', 'posts.title'])
            ->join('posts', 'users.id', '=', 'posts.user_id')
            ->get();
        
        $this->assertCount(3, $results);
        $this->assertEquals('User 1', $results[0]['name']);
        $this->assertEquals('Post by User 1', $results[0]['title']);
    }
    
    public function testCount()
    {
        $this->createTestTable('test_table', ['name', 'value']);
        $this->insertTestData('test_table', [
            ['name' => 'test1', 'value' => 'value1'],
            ['name' => 'test2', 'value' => 'value2'],
            ['name' => 'test3', 'value' => 'value3'],
        ]);
        
        $count = $this->builder->table('test_table')->count();
        
        $this->assertEquals(3, $count);
    }
    
    public function testFirstMethod()
    {
        $this->createTestTable('test_table', ['name', 'value']);
        $this->insertTestData('test_table', [
            ['name' => 'test1', 'value' => 'value1'],
            ['name' => 'test2', 'value' => 'value2'],
        ]);
        
        $result = $this->builder->table('test_table')
            ->orderBy('name', 'asc')
            ->first();
        
        $this->assertIsArray($result);
        $this->assertEquals('test1', $result['name']);
    }
}
