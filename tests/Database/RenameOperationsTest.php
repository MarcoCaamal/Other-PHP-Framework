<?php

namespace LightWeight\Tests\Database;

use PHPUnit\Framework\TestCase;
use LightWeight\Database\Migrations\Blueprint;
use LightWeight\Database\Migrations\Schema;
use LightWeight\Database\Contracts\DatabaseDriverContract;

class RenameOperationsTest extends TestCase
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
     * Prueba la funcionalidad de renombrar tabla en casos complejos
     */
    public function testRenameTableWithData()
    {
        // Crear tabla con datos
        Schema::create('users_original', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
        });
        
        // Insertar datos
        \LightWeight\Database\DB::statement(
            "INSERT INTO users_original (name, email) VALUES (?, ?), (?, ?)", 
            ['Usuario 1', 'user1@example.com', 'Usuario 2', 'user2@example.com']
        );
        
        // Renombrar tabla
        Schema::rename('users_original', 'users_renamed');
        
        // Verificar que los datos persisten
        $results = \LightWeight\Database\DB::statement("SELECT * FROM users_renamed");
        $this->assertCount(2, $results);
        $this->assertEquals('Usuario 1', $results[0]['name']);
        $this->assertEquals('user2@example.com', $results[1]['email']);
        
        // Limpieza
        Schema::dropIfExists('users_renamed');
    }
    
    /**
     * Prueba renombrar columnas con preservación de datos
     */
    public function testRenameColumnPreservesData()
    {
        // Crear tabla de prueba
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_name');
            $table->decimal('price', 10, 2);
        });
        
        // Insertar datos
        \LightWeight\Database\DB::statement(
            "INSERT INTO products (product_name, price) VALUES (?, ?), (?, ?)", 
            ['Producto 1', 19.99, 'Producto 2', 29.99]
        );
        
        // Renombrar columna
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('product_name', 'title');
            $table->renameColumn('price', 'cost', 'DECIMAL', ['precision' => 10, 'scale' => 2]);
        });
        
        // Esperar un poco para asegurar que los cambios se apliquen
        sleep(1);
        
        // Verificar que los datos persisten
        $results = \LightWeight\Database\DB::statement("SELECT * FROM products");
        $this->assertCount(2, $results);
        $this->assertEquals('Producto 1', $results[0]['title']);
        
        // Usar una comparación aproximada para valores decimales debido a posibles problemas de precisión
        $this->assertEqualsWithDelta(29.99, (float)$results[1]['cost'], 0.1);
        
        // Limpieza
        Schema::dropIfExists('products');
    }
    
    /**
     * Prueba la funcionalidad de change para modificar tipos de columnas
     */
    public function testChangeColumnType()
    {
        // Crear tabla con una columna entera
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->integer('code');
            $table->string('description', 50);
        });
        
        // Insertar un valor numérico
        \LightWeight\Database\DB::statement(
            "INSERT INTO items (code, description) VALUES (?, ?)", 
            [12345, 'Item de prueba']
        );
        
        // Cambiar el tipo a VARCHAR
        Schema::table('items', function (Blueprint $table) {
            $table->change('code', 'VARCHAR', ['length' => 20]);
            $table->change('description', 'TEXT');
        });
        
        // Esperar un poco para asegurar que los cambios se apliquen
        sleep(1);
        
        // Verificar que el tipo ha cambiado pero el dato se preserva
        $columnType = $this->getColumnType('items', 'code');
        $this->assertStringContainsString('varchar', strtolower($columnType));
        
        $descColumnType = $this->getColumnType('items', 'description');
        $this->assertStringContainsString('text', strtolower($descColumnType));
        
        $result = \LightWeight\Database\DB::statement("SELECT * FROM items LIMIT 1");
        $this->assertEquals('12345', $result[0]['code']);
        
        // Limpieza
        Schema::dropIfExists('items');
    }
    
    /**
     * Prueba renombrar índices en tablas con relaciones
     */
    public function testRenameIndexWithForeignKeys()
    {
        // Crear tablas con relaciones
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
        });
        
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->integer('category_id');
            $table->index('category_id', 'category_idx');
            $table->foreign('category_id')->references('id')->on('categories');
        });
        
        // Renombrar el índice
        Schema::table('posts', function (Blueprint $table) {
            $table->renameIndex('category_idx', 'category_relation_idx');
        });
        
        // Verificar que el índice fue renombrado
        $this->assertTrue($this->indexExists('posts', 'category_relation_idx'));
        $this->assertFalse($this->indexExists('posts', 'category_idx'));
        
        // Limpieza
        Schema::dropIfExists('posts');
        Schema::dropIfExists('categories');
    }
    
    /**
     * Prueba combinada de múltiples operaciones de rename y change
     */
    public function testCombinedRenameAndChangeOperations()
    {
        // Crear tabla inicial
        Schema::create('original_table', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50);
            $table->integer('points');
            $table->index('username', 'user_idx');
        });
        
        // Insertar datos
        \LightWeight\Database\DB::statement(
            "INSERT INTO original_table (username, points) VALUES (?, ?)", 
            ['testuser', 100]
        );
        
        // Operación 1: Renombrar tabla
        Schema::rename('original_table', 'new_table');
        
        // Operación 2: Modificar y renombrar columnas e índice
        Schema::table('new_table', function (Blueprint $table) {
            $table->renameColumn('username', 'login');
            $table->renameIndex('user_idx', 'login_idx');
            $table->change('points', 'DECIMAL', ['precision' => 8, 'scale' => 2]);
        });
        
        // Verificaciones
        $this->assertTrue($this->tableExists('new_table'));
        $this->assertTrue($this->columnExists('new_table', 'login'));
        $this->assertTrue($this->indexExists('new_table', 'login_idx'));
        
        $columnType = $this->getColumnType('new_table', 'points');
        $this->assertStringContainsString('decimal', strtolower($columnType));
        
        // Verificar que los datos persisten
        $data = \LightWeight\Database\DB::statement("SELECT * FROM new_table");
        $this->assertEquals('testuser', $data[0]['login']);
        $this->assertEqualsWithDelta(100.0, (float)$data[0]['points'], 0.01);
        
        // Limpieza
        Schema::dropIfExists('new_table');
    }
    
    /**
     * Verifica si una tabla existe en la base de datos
     */
    protected function tableExists(string $tableName): bool
    {
        $result = \LightWeight\Database\DB::statement(
            "SELECT 1 FROM information_schema.tables 
            WHERE table_schema = DATABASE() AND table_name = ?", 
            [$tableName]
        );
        
        return !empty($result);
    }
    
    /**
     * Verifica si una columna existe en una tabla específica
     */
    protected function columnExists(string $tableName, string $columnName): bool
    {
        $result = \LightWeight\Database\DB::statement(
            "SELECT 1 FROM information_schema.columns 
            WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?", 
            [$tableName, $columnName]
        );
        
        return !empty($result);
    }
    
    /**
     * Obtiene el tipo de una columna
     */
    protected function getColumnType(string $tableName, string $columnName): string
    {
        $result = \LightWeight\Database\DB::statement(
            "SHOW COLUMNS FROM `$tableName` WHERE Field = ?", 
            [$columnName]
        );
        
        // Asegurar que accedemos a los datos correctamente, ya sea como array asociativo o como objeto
        if (isset($result[0])) {
            if (is_array($result[0]) && isset($result[0]['Type'])) {
                return $result[0]['Type'];
            } elseif (is_object($result[0]) && isset($result[0]->Type)) {
                return $result[0]->Type;
            }
        }
        
        return '';
    }
    
    /**
     * Verifica si un índice existe en una tabla específica
     */
    protected function indexExists(string $tableName, string $indexName): bool
    {
        $result = \LightWeight\Database\DB::statement(
            "SELECT 1 FROM information_schema.statistics 
            WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?", 
            [$tableName, $indexName]
        );
        
        return !empty($result);
    }
}