<?php

namespace LightWeight\Tests\Database;

use PHPUnit\Framework\TestCase;
use LightWeight\Database\Migrations\Blueprint;
use LightWeight\Database\Migrations\Schema;
use LightWeight\Database\Contracts\DatabaseDriverContract;

class BlueprintForeignKeyActionsTest extends TestCase
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
     * Test ON DELETE CASCADE action
     */
    public function testOnDeleteCascade()
    {
        $this->setupBasicSchema();
        
        // Test the cascading delete
        $this->driver->statement("INSERT INTO parent_table (name) VALUES ('Parent Record')");
        $parentId = $this->driver->lastInsertId();
        
        $this->driver->statement(
            "INSERT INTO child_cascade_delete (parent_id, name) VALUES (?, 'Child Record')",
            [$parentId]
        );
        
        // Verify child record exists
        $childCount = $this->driver->statement(
            "SELECT COUNT(*) as count FROM child_cascade_delete WHERE parent_id = ?",
            [$parentId]
        )[0]['count'];
        
        $this->assertEquals(1, $childCount, "Child record should exist before parent deletion");
        
        // Delete parent record
        $this->driver->statement("DELETE FROM parent_table WHERE id = ?", [$parentId]);
        
        // Verify child record was deleted via CASCADE
        $childCount = $this->driver->statement(
            "SELECT COUNT(*) as count FROM child_cascade_delete WHERE parent_id = ?",
            [$parentId]
        )[0]['count'];
        
        $this->assertEquals(0, $childCount, "Child record should be deleted when parent is deleted (CASCADE)");
    }
    
    /**
     * Test ON DELETE SET NULL action
     */
    public function testOnDeleteSetNull()
    {
        $this->setupBasicSchema();
        
        // Test the SET NULL behavior
        $this->driver->statement("INSERT INTO parent_table (name) VALUES ('Parent Record')");
        $parentId = $this->driver->lastInsertId();
        
        $this->driver->statement(
            "INSERT INTO child_set_null (parent_id, name) VALUES (?, 'Child Record')",
            [$parentId]
        );
        
        // Delete parent record
        $this->driver->statement("DELETE FROM parent_table WHERE id = ?", [$parentId]);
        
        // Verify child record's parent_id was set to NULL
        $childRecord = $this->driver->statement(
            "SELECT * FROM child_set_null WHERE name = 'Child Record'"
        )[0];
        
        $this->assertNull($childRecord['parent_id'], "Foreign key should be set to NULL when parent is deleted (SET NULL)");
    }
    
    /**
     * Test ON DELETE RESTRICT action
     */
    public function testOnDeleteRestrict()
    {
        $this->setupBasicSchema();
        
        // Test the RESTRICT behavior
        $this->driver->statement("INSERT INTO parent_table (name) VALUES ('Parent Record')");
        $parentId = $this->driver->lastInsertId();
        
        $this->driver->statement(
            "INSERT INTO child_restrict (parent_id, name) VALUES (?, 'Child Record')",
            [$parentId]
        );
        
        // Try to delete parent record (should fail)
        $exceptionThrown = false;
        try {
            $this->driver->statement("DELETE FROM parent_table WHERE id = ?", [$parentId]);
        } catch (\Exception $e) {
            $exceptionThrown = true;
        }
        
        $this->assertTrue($exceptionThrown, "Should not be able to delete parent record with RESTRICT constraint");
        
        // Verify parent and child records still exist
        $parentCount = $this->driver->statement(
            "SELECT COUNT(*) as count FROM parent_table WHERE id = ?",
            [$parentId]
        )[0]['count'];
        
        $childCount = $this->driver->statement(
            "SELECT COUNT(*) as count FROM child_restrict WHERE parent_id = ?",
            [$parentId]
        )[0]['count'];
        
        $this->assertEquals(1, $parentCount, "Parent record should still exist");
        $this->assertEquals(1, $childCount, "Child record should still exist");
    }
    
    /**
     * Test ON UPDATE CASCADE action
     */
    public function testOnUpdateCascade()
    {
        $this->setupBasicSchema();
        
        // Insert test data
        $this->driver->statement("INSERT INTO parent_table (id, name) VALUES (1, 'Parent Record')");
        
        $this->driver->statement(
            "INSERT INTO child_update_cascade (parent_id, name) VALUES (1, 'Child Record')"
        );
        
        // Update parent ID
        $this->driver->statement("UPDATE parent_table SET id = 999 WHERE id = 1");
        
        // Verify child record was updated via CASCADE
        $childRecord = $this->driver->statement(
            "SELECT * FROM child_update_cascade WHERE name = 'Child Record'"
        )[0];
        
        $this->assertEquals(999, $childRecord['parent_id'], "Child foreign key should be updated when parent key is updated (CASCADE)");
    }
    
    /**
     * Test combined ON DELETE SET NULL and ON UPDATE CASCADE
     */
    public function testCombinedActions()
    {
        $this->setupBasicSchema();
        
        // Insert test data
        $this->driver->statement("INSERT INTO parent_table (id, name) VALUES (1, 'Parent Record')");
        
        $this->driver->statement(
            "INSERT INTO child_combined_actions (parent_id, name) VALUES (1, 'Child Record')"
        );
        
        // First test: update parent ID (should cascade)
        $this->driver->statement("UPDATE parent_table SET id = 555 WHERE id = 1");
        
        $childRecord = $this->driver->statement(
            "SELECT * FROM child_combined_actions WHERE name = 'Child Record'"
        )[0];
        
        $this->assertEquals(555, $childRecord['parent_id'], "Foreign key should be updated when parent ID is updated (CASCADE)");
        
        // Second test: delete parent (should set NULL)
        $this->driver->statement("DELETE FROM parent_table WHERE id = 555");
        
        $childRecord = $this->driver->statement(
            "SELECT * FROM child_combined_actions WHERE name = 'Child Record'"
        )[0];
        
        $this->assertNull($childRecord['parent_id'], "Foreign key should be set to NULL when parent is deleted (SET NULL)");
    }
    
    /**
     * Test foreign key name generation with different actions
     */
    public function testForeignKeyNameGeneration()
    {
        // Create three similar tables with different actions, should have different constraint names
        Schema::create('parent', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        
        // Table 1: No actions
        Schema::create('child1', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_id');
            $table->string('name');
            
            $table->foreign('parent_id')
                  ->references('id')
                  ->on('parent');
        });
        
        // Table 2: With ON DELETE CASCADE
        Schema::create('child2', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_id');
            $table->string('name');
            
            $table->foreign('parent_id')
                  ->references('id')
                  ->onDelete('CASCADE')
                  ->on('parent');
        });
        
        // Table 3: With ON UPDATE CASCADE
        Schema::create('child3', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_id');
            $table->string('name');
            
            $table->foreign('parent_id')
                  ->references('id')
                  ->onUpdate('CASCADE')
                  ->on('parent');
        });
        
        // Get constraint names
        $child1Sql = $this->driver->statement("SHOW CREATE TABLE child1")[0]['Create Table'];
        $child2Sql = $this->driver->statement("SHOW CREATE TABLE child2")[0]['Create Table'];
        $child3Sql = $this->driver->statement("SHOW CREATE TABLE child3")[0]['Create Table'];
        
        // Extract constraint names
        preg_match('/CONSTRAINT `([^`]+)`/', $child1Sql, $matches1);
        preg_match('/CONSTRAINT `([^`]+)`/', $child2Sql, $matches2);
        preg_match('/CONSTRAINT `([^`]+)`/', $child3Sql, $matches3);
        
        $name1 = $matches1[1];
        $name2 = $matches2[1];
        $name3 = $matches3[1];
        
        // Ensure constraint names are different
        $this->assertNotEquals($name1, $name2, "Foreign key names should be different for different ON DELETE actions");
        $this->assertNotEquals($name1, $name3, "Foreign key names should be different for different ON UPDATE actions");
        $this->assertNotEquals($name2, $name3, "Foreign key names should be different for different combinations of actions");
        
        // Clean up
        Schema::dropIfExists('child1');
        Schema::dropIfExists('child2');
        Schema::dropIfExists('child3');
        Schema::dropIfExists('parent');
    }
    
    /**
     * Test validation of referential actions
     */
    public function testInvalidReferentialAction()
    {
        // Expect exception when an invalid action is provided
        $this->expectException(\InvalidArgumentException::class);
        
        Schema::create('test_invalid_action', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_id');
            
            $table->foreign('parent_id')
                  ->references('id')
                  ->onDelete('INVALID_ACTION') // This should throw an exception
                  ->on('parent_table');
        });
    }
    
    /**
     * Helper to set up basic schema for tests
     */
    private function setupBasicSchema()
    {
        // Drop tables if they exist to ensure clean state
        Schema::dropIfExists('child_combined_actions');
        Schema::dropIfExists('child_update_cascade');
        Schema::dropIfExists('child_restrict');
        Schema::dropIfExists('child_set_null');
        Schema::dropIfExists('child_cascade_delete');
        Schema::dropIfExists('parent_table');
        
        // Create parent table
        Schema::create('parent_table', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        
        // Create child tables with different foreign key actions
        
        // 1. ON DELETE CASCADE
        Schema::create('child_cascade_delete', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_id');
            $table->string('name');
            
            $table->foreign('parent_id')
                  ->references('id')
                  ->onDelete('CASCADE')
                  ->on('parent_table');
        });
        
        // 2. ON DELETE SET NULL
        Schema::create('child_set_null', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_id')->nullable();
            $table->string('name');
            
            $table->foreign('parent_id')
                  ->references('id')
                  ->onDelete('SET NULL')
                  ->on('parent_table');
        });
        
        // 3. ON DELETE RESTRICT
        Schema::create('child_restrict', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_id');
            $table->string('name');
            
            $table->foreign('parent_id')
                  ->references('id')
                  ->onDelete('RESTRICT')
                  ->on('parent_table');
        });
        
        // 4. ON UPDATE CASCADE
        Schema::create('child_update_cascade', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_id');
            $table->string('name');
            
            $table->foreign('parent_id')
                  ->references('id')
                  ->onUpdate('CASCADE')
                  ->on('parent_table');
        });
        
        // 5. Combined ON DELETE SET NULL and ON UPDATE CASCADE
        Schema::create('child_combined_actions', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_id')->nullable();
            $table->string('name');
            
            $table->foreign('parent_id')
                  ->references('id')
                  ->onDelete('SET NULL')
                  ->onUpdate('CASCADE')
                  ->on('parent_table');
        });
    }
}