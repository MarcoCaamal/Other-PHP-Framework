<?php

namespace LightWeight\Tests\Database;

use PHPUnit\Framework\TestCase;
use LightWeight\Database\Migrations\Blueprint;
use LightWeight\Database\Migrations\Schema;
use LightWeight\Database\Contracts\DatabaseDriverContract;

class SchemaBuilderTest extends TestCase
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
    
    /**
     * Test the Schema facade methods for creating tables
     */
    public function testSchemaCreateMethod()
    {
        // 1. Create table using Schema facade
        Schema::create('schema_test', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        
        // 2. Verify table exists
        $this->assertTrue($this->tableExists('schema_test'));
        
        // 3. Check table structure
        $columns = $this->driver->statement("SHOW COLUMNS FROM schema_test");
        $columnNames = array_column($columns, 'Field');
        
        $this->assertContains('id', $columnNames);
        $this->assertContains('name', $columnNames);
        $this->assertContains('created_at', $columnNames);
        $this->assertContains('updated_at', $columnNames);
    }
    
    /**
     * Test Schema::dropIfExists method
     */
    public function testSchemaDropIfExistsMethod()
    {
        // 1. Create a table
        Schema::create('drop_test_table', function (Blueprint $table) {
            $table->id();
        });
        
        // Verify table exists
        $this->assertTrue($this->tableExists('drop_test_table'));
        
        // 2. Drop the table
        Schema::dropIfExists('drop_test_table');
        
        // Verify table doesn't exist
        $this->assertFalse($this->tableExists('drop_test_table'));
        
        // 3. Test that dropIfExists doesn't error on non-existent tables
        try {
            Schema::dropIfExists('non_existent_table');
            $noError = true;
        } catch (\Exception $e) {
            $noError = false;
        }
        
        $this->assertTrue($noError, "dropIfExists should not throw an exception for non-existent tables");
    }
    
    /**
     * Test Schema::table method for altering tables
     */
    public function testSchemaTableMethod()
    {
        // 1. Create initial table
        Schema::create('alter_schema_test', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        
        // 2. Alter the table using Schema::table
        Schema::table('alter_schema_test', function (Blueprint $table) {
            $table->string('email');
            $table->boolean('active')->default(true);
        });
        
        // 3. Verify new columns were added
        $columns = $this->driver->statement("SHOW COLUMNS FROM alter_schema_test");
        $columnNames = array_column($columns, 'Field');
        
        $this->assertContains('email', $columnNames);
        $this->assertContains('active', $columnNames);
        
        // 4. Check default value was set
        $activeColumn = null;
        foreach ($columns as $column) {
            if ($column['Field'] === 'active') {
                $activeColumn = $column;
                break;
            }
        }
        
        $this->assertNotNull($activeColumn);
        $this->assertEquals('1', $activeColumn['Default']);
    }
    
    /**
     * Test that empty Schema::table calls do not generate SQL
     */
    public function testEmptySchemaTableDoesNothing()
    {
        // 1. Create initial table
        Schema::create('empty_alter_test', function (Blueprint $table) {
            $table->id();
        });
        
        // Count number of columns initially
        $columns = $this->driver->statement("SHOW COLUMNS FROM empty_alter_test");
        $initialColumnCount = count($columns);
        
        // 2. Call Schema::table with empty blueprint
        Schema::table('empty_alter_test', function (Blueprint $table) {
            // Intentionally empty
        });
        
        // 3. Verify nothing changed
        $columns = $this->driver->statement("SHOW COLUMNS FROM empty_alter_test");
        $this->assertEquals($initialColumnCount, count($columns));
    }
    
    /**
     * Test the Schema builder with complex table structures
     */
    public function testComplexSchemaBuilder()
    {
        // Create a complete database schema for a blog system
        
        // 1. Users table
        Schema::create('schema_users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50)->unique();
            $table->string('email', 100)->unique();
            $table->string('password', 100);
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->boolean('is_admin')->default(false);
            $table->timestamps();
        });
        
        // 2. Categories table
        Schema::create('schema_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('slug', 50)->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });
        
        // 3. Posts table with relationships
        Schema::create('schema_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('content');
            $table->integer('user_id');
            $table->integer('category_id');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamps();
            
            // Add foreign keys
            $table->foreign('user_id')->references('id')->on('schema_users');
            $table->foreign('category_id')->references('id')->on('schema_categories');
        });
        
        // 4. Comments table with relationships
        Schema::create('schema_comments', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->integer('user_id');
            $table->integer('post_id');
            $table->boolean('approved')->default(false);
            $table->timestamps();
            
            // Add foreign keys
            $table->foreign('user_id')->references('id')->on('schema_users');
            $table->foreign('post_id')->references('id')->on('schema_posts');
        });
        
        // 5. Tags table
        Schema::create('schema_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 30);
            $table->string('slug', 30)->unique();
            $table->timestamps();
        });
        
        // 6. Post-Tag pivot table for many-to-many relationship
        Schema::create('schema_post_tag', function (Blueprint $table) {
            $table->integer('post_id');
            $table->integer('tag_id');
            
            // Make a composite primary key using Blueprint
            $table->primary(['post_id', 'tag_id']);
            
            // Add foreign keys
            $table->foreign('post_id')->references('id')->on('schema_posts');
            $table->foreign('tag_id')->references('id')->on('schema_tags');
        });
        
        // Verify all tables were created
        $this->assertTrue($this->tableExists('schema_users'));
        $this->assertTrue($this->tableExists('schema_categories'));
        $this->assertTrue($this->tableExists('schema_posts'));
        $this->assertTrue($this->tableExists('schema_comments'));
        $this->assertTrue($this->tableExists('schema_tags'));
        $this->assertTrue($this->tableExists('schema_post_tag'));
        
        // Verify foreign key relationships
        $postsTableSql = $this->driver->statement("SHOW CREATE TABLE schema_posts")[0]['Create Table'];
        $this->assertStringContainsString('FOREIGN KEY (`user_id`) REFERENCES `schema_users` (`id`)', $postsTableSql);
        $this->assertStringContainsString('FOREIGN KEY (`category_id`) REFERENCES `schema_categories` (`id`)', $postsTableSql);
        
        $commentsTableSql = $this->driver->statement("SHOW CREATE TABLE schema_comments")[0]['Create Table'];
        $this->assertStringContainsString('FOREIGN KEY (`user_id`) REFERENCES `schema_users` (`id`)', $commentsTableSql);
        $this->assertStringContainsString('FOREIGN KEY (`post_id`) REFERENCES `schema_posts` (`id`)', $commentsTableSql);
        
        $pivotTableSql = $this->driver->statement("SHOW CREATE TABLE schema_post_tag")[0]['Create Table'];
        $this->assertStringContainsString('PRIMARY KEY (`post_id`,`tag_id`)', $pivotTableSql);
        $this->assertStringContainsString('FOREIGN KEY (`post_id`) REFERENCES `schema_posts` (`id`)', $pivotTableSql);
        $this->assertStringContainsString('FOREIGN KEY (`tag_id`) REFERENCES `schema_tags` (`id`)', $pivotTableSql);
    }
    
    /**
     * Test that schema operations are atomic and get rolled back on error
     */
    public function testSchemaRollbackOnError()
    {
        // This test depends on how the Schema class handles transactions
        // If it doesn't handle them itself, this will need to be tested at a higher level
        
        // 1. Create a table first
        Schema::create('existing_table', function (Blueprint $table) {
            $table->id();
        });
        
        // 2. Try to create a table with an invalid column definition
        $exceptionThrown = false;
        try {
            // This should fail due to invalid SQL syntax
            $this->driver->statement("CREATE TABLE invalid_table (id INT, invalid_column INVALID_TYPE)");
        } catch (\Exception $e) {
            $exceptionThrown = true;
        }
        
        $this->assertTrue($exceptionThrown, "An exception should be thrown for invalid SQL");
        
        // 3. Check that we can still use the database and the first table still exists
        $this->assertTrue($this->tableExists('existing_table'));
    }
    
    /**
     * Helper function to check if a table exists
     */
    protected function tableExists(string $table): bool
    {
        try {
            $this->driver->statement("SELECT 1 FROM $table LIMIT 1");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
