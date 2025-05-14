<?php

namespace LightWeight\Tests\Database;

use PHPUnit\Framework\TestCase;
use LightWeight\Database\Migrations\Blueprint;
use LightWeight\Database\Migrations\Schema;
use LightWeight\Database\Contracts\DatabaseDriverContract;

class BlueprintForeignKeyTest extends TestCase
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
     * Test creating tables with foreign key relationships
     */
    public function testForeignKeyCreationWithReferences()
    {
        // 1. Create a users table first
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamps();
        });
        
        // 2. Create a posts table that references users
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->integer('user_id');
            $table->timestamps();
            
            // Add foreign key reference
            $table->foreign('user_id')->references('id')->on('users');
        });
        
        // 3. Create a comments table with multiple foreign keys
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->text('comment');
            $table->integer('user_id');
            $table->integer('post_id');
            $table->timestamps();
            
            // Add multiple foreign key references
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('post_id')->references('id')->on('posts');
        });
        
        // Verify structure of posts table
        $createPostsTableSql = $this->driver->statement("SHOW CREATE TABLE posts")[0]['Create Table'];
        
        // Verify foreign key constraint exists in posts table
        $this->assertStringContainsString('FOREIGN KEY', $createPostsTableSql);
        $this->assertStringContainsString('`user_id`', $createPostsTableSql);
        $this->assertStringContainsString('REFERENCES `users` (`id`)', $createPostsTableSql);
        
        // Verify structure of comments table
        $createCommentsTableSql = $this->driver->statement("SHOW CREATE TABLE comments")[0]['Create Table'];
        
        // Verify multiple foreign key constraints exist in comments table
        $this->assertStringContainsString('FOREIGN KEY', $createCommentsTableSql);
        $this->assertStringContainsString('`user_id`', $createCommentsTableSql);
        $this->assertStringContainsString('REFERENCES `users` (`id`)', $createCommentsTableSql);
        $this->assertStringContainsString('`post_id`', $createCommentsTableSql);
        $this->assertStringContainsString('REFERENCES `posts` (`id`)', $createCommentsTableSql);
    }
    
    /**
     * Test handling dependencies when dropping tables with foreign keys
     */
    public function testForeignKeyDependencyManagement()
    {
        // 1. Create related tables
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->integer('author_id');
            $table->foreign('author_id')->references('id')->on('authors');
        });
        
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->integer('book_id');
            $table->integer('rating');
            $table->text('content');
            $table->foreign('book_id')->references('id')->on('books');
        });
        
        // 2. Try to drop tables in the wrong order (should fail)
        $exceptionThrown = false;
        try {
            // This should fail because books table has a foreign key reference from reviews
            Schema::dropIfExists('authors');
        } catch (\Exception $e) {
            $exceptionThrown = true;
        }
        
        // Verify that exception was thrown due to foreign key constraint
        $this->assertTrue($exceptionThrown, "Expected an exception when trying to drop a table with dependent foreign keys");
        
        // 3. Drop tables in the correct order
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('books');
        Schema::dropIfExists('authors');
        
        // Verify all tables were successfully dropped
        $this->assertFalse($this->tableExists('reviews'));
        $this->assertFalse($this->tableExists('books'));
        $this->assertFalse($this->tableExists('authors'));
    }
    
    /**
     * Test adding foreign keys to existing tables
     */
    public function testAddForeignKeyToExistingTable()
    {
        // 1. Create tables without foreign keys first
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('category_id');
        });
        
        // 2. Verify that no foreign key exists initially
        $createTableSql = $this->driver->statement("SHOW CREATE TABLE products")[0]['Create Table'];
        $this->assertStringNotContainsString('FOREIGN KEY', $createTableSql);
        
        // 3. Add foreign key to existing table
        Schema::table('products', function (Blueprint $table) {
            $table->foreign('category_id')->references('id')->on('categories');
        });
        
        // 4. Verify foreign key was added
        $updatedTableSql = $this->driver->statement("SHOW CREATE TABLE products")[0]['Create Table'];
        $this->assertStringContainsString('FOREIGN KEY', $updatedTableSql);
        $this->assertStringContainsString('`category_id`', $updatedTableSql);
        $this->assertStringContainsString('REFERENCES `categories` (`id`)', $updatedTableSql);
    }
    
    /**
     * Test creating a table with composite foreign keys
     */
    public function testCompositeForeignKeys()
    {
        // 1. Create a table with composite primary key
        Schema::create('order_items', function (Blueprint $table) {
            $table->integer('order_id');
            $table->integer('product_id');
            $table->integer('quantity');
            
            // Use primary() method from Blueprint
            $table->primary(['order_id', 'product_id']);
        });
        
        // 2. Create a table with a foreign key to the composite key
        Schema::create('order_item_notes', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id');
            $table->integer('product_id');
            $table->text('note');
            
            // Use the Blueprint foreign method 
            $table->foreign(['order_id', 'product_id'])
                  ->references(['order_id', 'product_id'])
                  ->on('order_items');
        });
        
        // 3. Verify the foreign key exists
        $createTableSql = $this->driver->statement("SHOW CREATE TABLE order_item_notes")[0]['Create Table'];
        $this->assertStringContainsString('FOREIGN KEY', $createTableSql);
        $this->assertStringContainsString('`order_id`, `product_id`', $createTableSql);
        $this->assertStringContainsString('REFERENCES `order_items`', $createTableSql);
    }
    
    /**
     * Test modifying foreign key constraints (will require direct SQL until Blueprint supports it)
     */
    public function testModifyForeignKeyConstraints()
    {
        // 1. Set up tables with a foreign key
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        
        Schema::create('employees', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->primary('id'); // Make it auto-increment and primary key
            $table->string('name');
            $table->integer('department_id');
            $table->foreign('department_id')->references('id')->on('departments');
        });
        
        // 2. Modify the foreign key to add ON DELETE CASCADE (using direct SQL)
        $this->driver->statement(
            "ALTER TABLE employees DROP FOREIGN KEY `fk_employees_departments_department_id`"
        );
        
        $this->driver->statement(
            "ALTER TABLE employees ADD CONSTRAINT `fk_employees_departments_department_id` " .
            "FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE CASCADE"
        );
        
        // 3. Verify the constraint was changed
        $createTableSql = $this->driver->statement("SHOW CREATE TABLE employees")[0]['Create Table'];
        $this->assertStringContainsString('ON DELETE CASCADE', $createTableSql);
        
        // 4. Test the cascade works by deleting a department
        // First, insert test data
        $this->driver->statement("INSERT INTO departments (name) VALUES ('Engineering')");
        $departmentId = $this->driver->lastInsertId();
        
        $this->driver->statement(
            "INSERT INTO employees (id, name, department_id) VALUES (1, 'John Doe', ?)", 
            [$departmentId]
        );
        
        // Verify employee exists
        $employeeCount = $this->driver->statement(
            "SELECT COUNT(*) as count FROM employees WHERE department_id = ?", 
            [$departmentId]
        )[0]['count'];
        
        $this->assertEquals(1, $employeeCount);
        
        // Delete department and verify employee is cascaded
        $this->driver->statement("DELETE FROM departments WHERE id = ?", [$departmentId]);
        
        $remainingEmployees = $this->driver->statement(
            "SELECT COUNT(*) as count FROM employees WHERE department_id = ?", 
            [$departmentId]
        )[0]['count'];
        
        $this->assertEquals(0, $remainingEmployees);
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
