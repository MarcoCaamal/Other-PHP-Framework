<?php

namespace LightWeight\Tests\Database;

use PHPUnit\Framework\TestCase;
use LightWeight\Database\Migrations\Blueprint;
use LightWeight\Database\Migrations\Schema;
use LightWeight\Database\Contracts\DatabaseDriverContract;

class BlueprintIndexTest extends TestCase
{
    use RefreshDatabase {
        setUp as protected dbSetUp;
        tearDown as protected dbTearDown;
    }
    
    protected ?DatabaseDriverContract $driver = null;
    
    protected function setUp(): void
    {
        $this->dbSetUp();
    }
    
    protected function tearDown(): void
    {
        $this->dbTearDown();
    }
    
    public function testCreateIndexOnTable()
    {
        // Create a table with an index
        Schema::create('test_indexes', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('username');
            
            // Add an index
            $table->index('username');
        });
        
        // Verify the index exists
        $indexes = $this->driver->statement("SHOW INDEX FROM test_indexes");
        
        $hasIndex = false;
        foreach ($indexes as $index) {
            if ($index['Key_name'] === 'test_indexes_username_index') {
                $hasIndex = true;
                break;
            }
        }
        
        $this->assertTrue($hasIndex, 'Index was not created on the table');
    }
    
    public function testCreateCompositeIndex()
    {
        // Create a table with a composite index
        Schema::create('test_composite_indexes', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            
            // Add composite index
            $table->index(['first_name', 'last_name'], 'name_index');
        });
        
        // Verify the index exists
        $indexes = $this->driver->statement("SHOW INDEX FROM test_composite_indexes");
        
        $indexColumns = [];
        foreach ($indexes as $index) {
            if ($index['Key_name'] === 'name_index') {
                $indexColumns[] = $index['Column_name'];
            }
        }
        
        $this->assertCount(2, $indexColumns);
        $this->assertContains('first_name', $indexColumns);
        $this->assertContains('last_name', $indexColumns);
    }
    
    public function testCreateUniqueIndex()
    {
        // Create a table with a unique index
        Schema::create('test_unique_indexes', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            
            // Add unique index
            $table->uniqueIndex('email');
        });
        
        // Verify the index exists and is unique
        $indexes = $this->driver->statement("SHOW INDEX FROM test_unique_indexes");
        
        $foundUniqueIndex = false;
        foreach ($indexes as $index) {
            if ($index['Key_name'] === 'test_unique_indexes_email_unique' && $index['Non_unique'] == 0) {
                $foundUniqueIndex = true;
                break;
            }
        }
        
        $this->assertTrue($foundUniqueIndex, 'Unique index was not created correctly');
    }
    
    public function testSetPrimaryKeyOnSpecificColumns()
    {
        // Create a table with a specific primary key
        Schema::create('test_primary_key', function (Blueprint $table) {
            $table->string('code');
            $table->string('name');
            
            // Set primary key
            $table->primary('code');
        });
        
        // Verify the primary key
        $indexes = $this->driver->statement("SHOW INDEX FROM test_primary_key");
        
        $primaryKeyColumn = null;
        foreach ($indexes as $index) {
            if ($index['Key_name'] === 'PRIMARY') {
                $primaryKeyColumn = $index['Column_name'];
                break;
            }
        }
        
        $this->assertEquals('code', $primaryKeyColumn);
    }
    
    public function testCompositePrimaryKey()
    {
        // Create a table with a composite primary key
        Schema::create('test_composite_pk', function (Blueprint $table) {
            $table->integer('product_id');
            $table->integer('category_id');
            $table->string('name');
            
            // Set composite primary key
            $table->primary(['product_id', 'category_id']);
        });
        
        // Verify the primary key
        $indexes = $this->driver->statement("SHOW INDEX FROM test_composite_pk");
        
        $primaryKeyColumns = [];
        foreach ($indexes as $index) {
            if ($index['Key_name'] === 'PRIMARY') {
                $primaryKeyColumns[] = $index['Column_name'];
            }
        }
        
        $this->assertCount(2, $primaryKeyColumns);
        $this->assertContains('product_id', $primaryKeyColumns);
        $this->assertContains('category_id', $primaryKeyColumns);
    }
    
    public function testAddIndexToExistingTable()
    {
        // Create table first
        Schema::create('add_index_later', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
        });
        
        // Add index later
        Schema::table('add_index_later', function (Blueprint $table) {
            $table->index('email', 'email_idx');
        });
        
        // Verify the index exists
        $indexes = $this->driver->statement("SHOW INDEX FROM add_index_later");
        
        $hasIndex = false;
        foreach ($indexes as $index) {
            if ($index['Key_name'] === 'email_idx') {
                $hasIndex = true;
                break;
            }
        }
        
        $this->assertTrue($hasIndex, 'Index was not added to the existing table');
    }
    
    public function testDropIndexFromTable()
    {
        // Create table with index
        Schema::create('drop_index_test', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->index('name', 'name_idx');
        });
        
        // Verify index exists initially
        $indexes = $this->driver->statement("SHOW INDEX FROM drop_index_test");
        $initiallyHasIndex = false;
        foreach ($indexes as $index) {
            if ($index['Key_name'] === 'name_idx') {
                $initiallyHasIndex = true;
                break;
            }
        }
        $this->assertTrue($initiallyHasIndex, 'Index was not created initially');
        
        // Now drop the index
        Schema::table('drop_index_test', function (Blueprint $table) {
            $table->dropIndex(null, 'name_idx');
        });
        
        // Verify index is gone
        $indexes = $this->driver->statement("SHOW INDEX FROM drop_index_test");
        $stillHasIndex = false;
        foreach ($indexes as $index) {
            if ($index['Key_name'] === 'name_idx') {
                $stillHasIndex = true;
                break;
            }
        }
        
        $this->assertFalse($stillHasIndex, 'Index was not dropped from the table');
    }
    
    public function testColumnModifiers()
    {
        // Create table with column modifiers
        Schema::create('column_modifiers_test', function (Blueprint $table) {
            $table->integer('id')->primary('id')->autoIncrement(); // This will be the primary key
            $table->integer('counter')->unsigned()->default(0);
            $table->string('title')->nullable()->comment('Post title');
            $table->string('slug')->unique();
        });
        
        // Verify column attributes
        $columns = $this->driver->statement("SHOW COLUMNS FROM column_modifiers_test");
        $columnMap = [];
        foreach ($columns as $column) {
            $columnMap[$column['Field']] = [
                'Type' => $column['Type'],
                'Null' => $column['Null'],
                'Default' => $column['Default'],
                'Extra' => $column['Extra'],
            ];
        }
        
        // Check unsigned modifier
        $this->assertStringContainsString('unsigned', strtolower($columnMap['counter']['Type']));
        
        // Check default value
        $this->assertEquals('0', $columnMap['counter']['Default']);
        
        // Check nullable
        $this->assertEquals('YES', $columnMap['title']['Null']);
        
        // Check auto increment
        $this->assertStringContainsString('auto_increment', strtolower($columnMap['id']['Extra']));
        
        // Check comment
        $createTableSql = $this->driver->statement("SHOW CREATE TABLE column_modifiers_test")[0]['Create Table'];
        $this->assertStringContainsString("COMMENT 'Post title'", $createTableSql);
        
        // Check unique constraint
        $indexes = $this->driver->statement("SHOW INDEX FROM column_modifiers_test");
        $hasUniqueIndex = false;
        foreach ($indexes as $index) {
            if ($index['Column_name'] === 'slug' && $index['Non_unique'] == 0) {
                $hasUniqueIndex = true;
                break;
            }
        }
        $this->assertTrue($hasUniqueIndex);
    }
}
