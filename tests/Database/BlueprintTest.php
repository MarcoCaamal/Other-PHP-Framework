<?php

namespace LightWeight\Tests\Database;

use PHPUnit\Framework\TestCase;
use LightWeight\Database\Migrations\Blueprint;
use LightWeight\Database\Migrations\Schema;
use LightWeight\Database\Contracts\DatabaseDriverContract;
use PHPUnit\Framework\Attributes\DataProvider;

class BlueprintTest extends TestCase
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
    
    public function testCreateTable()
    {
        // Probar la creación de una tabla con diferentes tipos de columnas
        Schema::create('test_table', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('age');
            $table->boolean('active');
            $table->text('description');
            $table->decimal('price', 8, 2);
            $table->datetime('created_at');
            $table->date('birth_date');
            $table->timestamp('logged_at');
            $table->enum('status', ['pending', 'active', 'cancelled']);
        });
        
        // Verificar que la tabla fue creada
        try {
            $columns = $this->driver->statement("SHOW COLUMNS FROM test_table");
            
            // Mapear los nombres de las columnas y sus tipos
            $columnMap = [];
            foreach ($columns as $column) {
                $columnMap[$column['Field']] = $column['Type'];
            }
            
            // Verificar que existen las columnas con los tipos correctos
            $this->assertEquals(10, count($columns));
            $this->assertArrayHasKey('id', $columnMap);
            $this->assertArrayHasKey('name', $columnMap);
            $this->assertArrayHasKey('age', $columnMap);
            $this->assertArrayHasKey('active', $columnMap);
            $this->assertArrayHasKey('description', $columnMap);
            $this->assertArrayHasKey('price', $columnMap);
            $this->assertArrayHasKey('created_at', $columnMap);
            $this->assertArrayHasKey('birth_date', $columnMap);
            $this->assertArrayHasKey('logged_at', $columnMap);
            $this->assertArrayHasKey('status', $columnMap);
            
            // Verificar los tipos de datos
            $this->assertStringContainsString('int', $columnMap['id']);
            $this->assertStringContainsString('varchar', $columnMap['name']);
            $this->assertStringContainsString('int', $columnMap['age']);
            $this->assertStringContainsString('tinyint', $columnMap['active']);
            $this->assertStringContainsString('text', $columnMap['description']);
            $this->assertStringContainsString('decimal', $columnMap['price']);
            $this->assertStringContainsString('datetime', $columnMap['created_at']);
            $this->assertStringContainsString('date', $columnMap['birth_date']);
            $this->assertStringContainsString('timestamp', $columnMap['logged_at']);
            $this->assertStringContainsString("enum('pending','active','cancelled')", $columnMap['status']);
            
        } catch (\Exception $e) {
            $this->fail("Failed to create table: " . $e->getMessage());
        }
    }
    
    public function testCreateTableWithNullableColumns()
    {
        // Probar la creación de una tabla con columnas nulas y valores por defecto
        Schema::create('nullable_table', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->integer('count')->default(0);
            $table->boolean('active')->default(true);
            $table->string('code')->nullable()->default('N/A');
            $table->timestamps();
        });
        
        // Verificar las columnas
        $columns = $this->driver->statement("SHOW COLUMNS FROM nullable_table");
        
        // Mapear detalles de columnas
        $columnDetails = [];
        foreach ($columns as $column) {
            $columnDetails[$column['Field']] = [
                'type' => $column['Type'],
                'null' => $column['Null'],
                'default' => $column['Default']
            ];
        }
        
        // Verificar características de las columnas
        $this->assertEquals('YES', $columnDetails['name']['null']); // Nullable
        $this->assertEquals('NO', $columnDetails['count']['null']); // Not nullable 
        $this->assertEquals('0', $columnDetails['count']['default']); // Default value
        $this->assertEquals('1', $columnDetails['active']['default']); // Default value
        $this->assertEquals('N/A', $columnDetails['code']['default']); // Default value
        $this->assertEquals('YES', $columnDetails['code']['null']); // Nullable
    }
    
    public function testAlterTable()
    {
        // Crear una tabla inicial
        Schema::create('alter_test', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        
        // Modificar la tabla añadiendo columnas
        Schema::table('alter_test', function (Blueprint $table) {
            $table->string('email');
            $table->boolean('verified')->default(false);
        });
        
        // Verificar que las nuevas columnas existen
        $columns = $this->driver->statement("SHOW COLUMNS FROM alter_test");
        $columnNames = array_column($columns, 'Field');
        
        $this->assertContains('email', $columnNames);
        $this->assertContains('verified', $columnNames);
    }
    
    public function testDropColumn()
    {
        // Crear una tabla con varias columnas
        Schema::create('drop_test', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->timestamps();
        });
        
        // Verificar columnas iniciales
        $initialColumns = $this->driver->statement("SHOW COLUMNS FROM drop_test");
        $this->assertEquals(6, count($initialColumns));
        
        // Eliminar una columna
        Schema::table('drop_test', function (Blueprint $table) {
            $table->dropColumn('phone');
        });
        
        // Verificar que la columna fue eliminada
        $remainingColumns = $this->driver->statement("SHOW COLUMNS FROM drop_test");
        $columnNames = array_column($remainingColumns, 'Field');
        
        $this->assertEquals(5, count($remainingColumns));
        $this->assertNotContains('phone', $columnNames);
    }
    
    public function testDropMultipleColumns()
    {
        // Crear una tabla con varias columnas
        Schema::create('multi_drop_test', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('middle_name');
            $table->string('last_name');
            $table->string('email');
            $table->timestamps();
        });
        
        // Eliminar múltiples columnas
        Schema::table('multi_drop_test', function (Blueprint $table) {
            $table->dropColumn(['middle_name', 'last_name']);
        });
        
        // Verificar que las columnas fueron eliminadas
        $remainingColumns = $this->driver->statement("SHOW COLUMNS FROM multi_drop_test");
        $columnNames = array_column($remainingColumns, 'Field');
        
        $this->assertEquals(5, count($remainingColumns)); // id, first_name, email, created_at, updated_at
        $this->assertNotContains('middle_name', $columnNames);
        $this->assertNotContains('last_name', $columnNames);
    }
    
    public function testUniqueConstraints()
    {
        // Crear tabla con restricciones únicas
        Schema::create('unique_test', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('username', 50)->unique();
        });
        
        // Verificar que las columnas tienen restricciones únicas
        $createTableSql = $this->driver->statement("SHOW CREATE TABLE unique_test")[0]['Create Table'];
        
        $this->assertStringContainsString('UNIQUE KEY', $createTableSql);
        $this->assertStringContainsString('`email`', $createTableSql);
        $this->assertStringContainsString('`username`', $createTableSql);
    }
    
    public function testForeignKeys()
    {
        // Crear tablas para probar claves foráneas
        Schema::create('parent_table', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        
        Schema::create('child_table', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_id');
            $table->string('description');
            
            // Añadir clave foránea
            $table->foreign('parent_id')->references('id')->on('parent_table');
        });
        
        // Verificar la existencia de la clave foránea
        $createTableSql = $this->driver->statement("SHOW CREATE TABLE child_table")[0]['Create Table'];
        
        $this->assertStringContainsString('FOREIGN KEY', $createTableSql);
        $this->assertStringContainsString('`parent_id`', $createTableSql);
        $this->assertStringContainsString('`parent_table`', $createTableSql);
    }
    
    public function testEngineAndCharset()
    {
        // Crear tabla con motor y conjunto de caracteres específicos
        Schema::create('custom_engine_table', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            
            $table->engine('InnoDB');
            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');
        });
        
        // Verificar configuraciones de tabla
        $tableInfo = $this->driver->statement("SHOW TABLE STATUS WHERE Name = 'custom_engine_table'")[0];
        $createTableSql = $this->driver->statement("SHOW CREATE TABLE custom_engine_table")[0]['Create Table'];
        
        $this->assertEquals('InnoDB', $tableInfo['Engine']);
        $this->assertStringContainsString('DEFAULT CHARSET=utf8mb4', $createTableSql);
        $this->assertStringContainsString('COLLATE=utf8mb4_unicode_ci', $createTableSql);
    }
    
    public function testDropAndRecreateTable()
    {
        // Crear tabla
        Schema::create('temp_table', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        
        // Verificar que existe
        $this->assertTrue($this->tableExists('temp_table'));
        
        // Eliminar tabla
        Schema::dropIfExists('temp_table');
        
        // Verificar que no existe
        $this->assertFalse($this->tableExists('temp_table'));
        
        // Volver a crear
        Schema::create('temp_table', function (Blueprint $table) {
            $table->id();
            $table->string('email');
        });
        
        // Verificar que existe nuevamente
        $this->assertTrue($this->tableExists('temp_table'));
        
        // Verificar la nueva estructura
        $columns = $this->driver->statement("SHOW COLUMNS FROM temp_table");
        $columnNames = array_column($columns, 'Field');
        
        $this->assertContains('email', $columnNames);
        $this->assertNotContains('name', $columnNames);
    }
    
    /**
     * Comprueba si una tabla existe en la base de datos
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
