<?php

namespace LightWeight\Tests\Database;

use PHPUnit\Framework\TestCase;
use LightWeight\Database\Migrations\Blueprint;
use LightWeight\Database\Migrations\Schema;
use LightWeight\Database\Contracts\DatabaseDriverContract;
use PHPUnit\Framework\Attributes\DataProvider;

class BlueprintAdvancedTest extends TestCase
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
     * Prueba la creación y compilación de un comando SQL para una tabla nueva
     */
    public function testBlueprintToSql()
    {
        // Crear una instancia de Blueprint directamente
        $blueprint = new Blueprint('test_blueprint');
        $blueprint->id();
        $blueprint->string('name');
        $blueprint->integer('age');
        
        // Obtener el SQL generado
        $sql = $blueprint->toSql();
        
        // Verificar que el SQL contenga los elementos esperados
        $this->assertStringContainsString('CREATE TABLE test_blueprint', $sql);
        $this->assertStringContainsString('`id`', $sql);
        $this->assertStringContainsString('`name`', $sql);
        $this->assertStringContainsString('`age`', $sql);
        $this->assertStringContainsString('AUTO_INCREMENT', $sql);
        $this->assertStringContainsString('PRIMARY KEY', $sql);
        $this->assertStringContainsString('VARCHAR', $sql);
        $this->assertStringContainsString('INT', $sql);
    }
    
    /**
     * Prueba el método de compilación del esquema de alteración de tabla
     */
    public function testAlterTableToSql()
    {
        // Crear una instancia de Blueprint para alteración
        $blueprint = new Blueprint('alter_test', 'alter');
        $blueprint->string('new_column');
        
        // Obtener SQL generado
        $sql = $blueprint->toSql();
        
        // Verificar SQL
        $this->assertStringContainsString('ALTER TABLE alter_test', $sql);
        $this->assertStringContainsString('ADD COLUMN', $sql);
        $this->assertStringContainsString('`new_column`', $sql);
        $this->assertStringContainsString('VARCHAR', $sql);
    }
    
    /**
     * Prueba la creación y eliminación de múltiples tablas en secuencia
     */
    public function testMultipleTableOperations()
    {
        // 1. Crear primera tabla
        Schema::create('table_one', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        
        // 2. Crear segunda tabla con referencia a la primera
        Schema::create('table_two', function (Blueprint $table) {
            $table->id();
            $table->integer('table_one_id');
            $table->string('description');
            
            $table->foreign('table_one_id')->references('id')->on('table_one');
        });
        
        // Verificar que ambas tablas existen
        $this->assertTrue($this->tableExists('table_one'));
        $this->assertTrue($this->tableExists('table_two'));
        
        // 3. Eliminar tablas en orden inverso (debido a la clave foránea)
        Schema::dropIfExists('table_two');
        Schema::dropIfExists('table_one');
        
        // Verificar que las tablas fueron eliminadas
        $this->assertFalse($this->tableExists('table_two'));
        $this->assertFalse($this->tableExists('table_one'));
    }
    
    /**
     * Prueba la generación de una estructura compleja de tabla usando diversos tipos de columna
     */
    public function testComplexTableSchema()
    {
        Schema::create('complex_table', function (Blueprint $table) {
            // Columna de ID
            $table->id();
            
            // Campos básicos con modificadores
            $table->string('username', 50)->unique();
            $table->string('email')->unique();
            $table->string('password');
            
            // Campos con valores por defecto
            $table->boolean('active')->default(true);
            $table->integer('login_count')->default(0);
            
            // Campos de fecha/hora
            $table->datetime('last_login')->nullable();
            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();
            
            // Campos de texto y contenido largo
            $table->text('biography')->nullable();
            
            // Campos numéricos
            $table->decimal('balance', 10, 2)->default(0.00);
            
            // Campos de enumeración
            $table->enum('role', ['user', 'admin', 'editor']);
            
            // Campos opcionales
            $table->string('phone')->nullable();
            $table->date('birthdate')->nullable();
            
            // Configuración de tabla
            $table->engine('InnoDB');
            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');
        });
        
        // Verificar la estructura de la tabla
        $columns = $this->driver->statement("SHOW FULL COLUMNS FROM complex_table");
        $columnNames = array_column($columns, 'Field');
        
        // Verificar que todas las columnas estén presentes
        $expectedColumns = [
            'id', 'username', 'email', 'password', 'active', 'login_count',
            'last_login', 'created_at', 'updated_at', 'biography',
            'balance', 'role', 'phone', 'birthdate'
        ];
        
        foreach ($expectedColumns as $columnName) {
            $this->assertContains($columnName, $columnNames, "La columna '$columnName' no se encontró en la tabla");
        }
        
        // Obtener información de columnas con SHOW FULL COLUMNS
        $columns = $this->driver->statement("SHOW FULL COLUMNS FROM complex_table");
        
        // Verificar restricciones únicas
        $uniqueColumns = array_filter($columns, fn($column) => $column['Key'] === 'UNI');
        $this->assertNotEmpty($uniqueColumns, "No se encontraron restricciones únicas");

        // Crear un mapa de columnas para fácil acceso
        $columnMap = [];
        foreach ($columns as $column) {
            $columnMap[$column['Field']] = $column;
        }
        
        // Verificar valores por defecto
        $this->assertEquals('1', $columnMap['active']['Default'], "El valor por defecto de 'active' no es '1'");
        $this->assertEquals('0', $columnMap['login_count']['Default'], "El valor por defecto de 'login_count' no es '0'");
        $this->assertEquals('0.00', $columnMap['balance']['Default'], "El valor por defecto de 'balance' no es '0.00'");
        
        // Verificar columnas que permiten NULL
        $this->assertEquals('YES', $columnMap['last_login']['Null'], "La columna 'last_login' no permite NULL");
        $this->assertEquals('YES', $columnMap['updated_at']['Null'], "La columna 'updated_at' no permite NULL");
        $this->assertEquals('YES', $columnMap['biography']['Null'], "La columna 'biography' no permite NULL");
        $this->assertEquals('YES', $columnMap['phone']['Null'], "La columna 'phone' no permite NULL");
        $this->assertEquals('YES', $columnMap['birthdate']['Null'], "La columna 'birthdate' no permite NULL");
        
        // Verificar el tipo de datos enum
        $this->assertEquals("enum('user','admin','editor')", strtolower($columnMap['role']['Type']), "El tipo de datos de 'role' no es el esperado");
        
        // Verificar configuración de la tabla
        $tableInfo = $this->driver->statement("SHOW TABLE STATUS LIKE 'complex_table'")[0];
        $this->assertEquals('InnoDB', $tableInfo['Engine'], "El motor de la tabla no es InnoDB");
        $this->assertEquals('utf8mb4_unicode_ci', $tableInfo['Collation'], "La colación de la tabla no es utf8mb4_unicode_ci");
    }
    
    /**
     * Prueba el método hasCommands de Blueprint
     */
    public function testBlueprintHasCommands()
    {
        // Blueprint vacío
        $emptyBlueprint = new Blueprint('empty_table');
        $this->assertFalse($emptyBlueprint->hasCommands());
        
        // Blueprint con comandos
        $filledBlueprint = new Blueprint('filled_table');
        $filledBlueprint->id();
        $filledBlueprint->string('name');
        $this->assertTrue($filledBlueprint->hasCommands());
    }
    
    /**
     * Prueba que la opción de autoincrementable solo se aplique a las columnas para las que tiene sentido
     */
    public function testAutoIncrementOnlyOnSupportedColumns()
    {
        Schema::create('increment_test', function (Blueprint $table) {
            $table->id(); // Debería ser autoincrementable
        });
        
        $columns = $this->driver->statement("SHOW COLUMNS FROM increment_test");
        $idColumnInfo = null;
        foreach ($columns as $column) {
            if ($column['Field'] === 'id') {
                $idColumnInfo = $column;
                break;
            }
        }
        
        $this->assertNotNull($idColumnInfo);
        $this->assertEquals('auto_increment', $idColumnInfo['Extra']);
    }
    
    /**
     * Prueba la funcionalidad de renombrar tabla
     */
    public function testRenameTable()
    {
        // Primero creamos una tabla de prueba
        Schema::create('test_rename_table', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        
        // Verificar que existe la tabla
        $this->assertTrue($this->tableExists('test_rename_table'));
        
        // Ahora renombramos la tabla
        Schema::rename('test_rename_table', 'test_renamed');
        
        // Verificar que la tabla original ya no existe
        $this->assertFalse($this->tableExists('test_rename_table'));
        
        // Verificar que la tabla renombrada existe
        $this->assertTrue($this->tableExists('test_renamed'));
        
        // Limpieza
        Schema::dropIfExists('test_renamed');
    }
    
    /**
     * Prueba la funcionalidad de renombrar columna
     */
    public function testRenameColumn()
    {
        // Primero creamos una tabla de prueba
        Schema::create('test_rename_column', function (Blueprint $table) {
            $table->id();
            $table->string('old_name');
        });
        
        // Verificar que existe la columna
        $this->assertTrue($this->columnExists('test_rename_column', 'old_name'));
        
        // Ahora renombramos la columna
        Schema::table('test_rename_column', function (Blueprint $table) {
            $table->renameColumn('old_name', 'new_name');
        });
        
        // Verificar que la columna original ya no existe
        $this->assertFalse($this->columnExists('test_rename_column', 'old_name'));
        
        // Verificar que la columna renombrada existe
        $this->assertTrue($this->columnExists('test_rename_column', 'new_name'));
        
        // Limpieza
        Schema::dropIfExists('test_rename_column');
    }
    
    /**
     * Prueba la funcionalidad de modificar columna
     */
    public function testChangeColumn()
    {
        // Primero creamos una tabla de prueba con una columna entera
        Schema::create('test_change_column', function (Blueprint $table) {
            $table->id();
            $table->integer('numeric_value');
        });
        
        // Modificar el tipo de la columna a VARCHAR
        Schema::table('test_change_column', function (Blueprint $table) {
            $table->change('numeric_value', 'VARCHAR', ['length' => 100]);
        });
        
        // Verificar que el tipo de columna se cambió correctamente
        $columnType = $this->getColumnType('test_change_column', 'numeric_value');
        $this->assertStringContainsString('varchar', strtolower($columnType));
        
        // Limpieza
        Schema::dropIfExists('test_change_column');
    }
    
    /**
     * Prueba la funcionalidad de renombrar índice
     */
    public function testRenameIndex()
    {
        // Primero creamos una tabla de prueba con un índice
        Schema::create('test_rename_index', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->index('email', 'old_index_name');
        });
        
        // Verificar que existe el índice
        $this->assertTrue($this->indexExists('test_rename_index', 'old_index_name'));
        
        // Ahora renombramos el índice
        Schema::table('test_rename_index', function (Blueprint $table) {
            $table->renameIndex('old_index_name', 'new_index_name');
        });
        
        // Verificar que el índice original ya no existe
        $this->assertFalse($this->indexExists('test_rename_index', 'old_index_name'));
        
        // Verificar que el índice renombrado existe
        $this->assertTrue($this->indexExists('test_rename_index', 'new_index_name'));
        
        // Limpieza
        Schema::dropIfExists('test_rename_index');
    }
    
    /**
     * Prueba el acortamiento de nombres para claves foráneas
     */
    public function testForeignKeyNameShortening()
    {
        // Crear una instancia de Blueprint para probar el método protegido usando reflexión
        $blueprint = new Blueprint('test_table');
        $reflectionClass = new \ReflectionClass(Blueprint::class);
        
        // Hacer accesible el método protegido shortenIdentifier
        $method = $reflectionClass->getMethod('shortenIdentifier');
        $method->setAccessible(true);
        
        // Probar varios casos de acortamiento
        $testCases = [
            'nombre_corto' => 'nombre_corto',           // No debería cambiarse
            'nombre_largo_que_necesita_acortarse' => 'nmbr_lrg_q',    // Debe acortarse
            'inventory_equipment_reference' => 'nvntry_qpm',         // Debe acortarse
            'usuarios_administradores_sistema' => 'srs_dmnstr',      // Debe acortarse
            'a' => 'a',                                 // No debería cambiarse
            'aeiou' => 'aeiou'                          // No debería cambiarse
        ];
        
        foreach ($testCases as $original => $expectedLength) {
            $shortened = $method->invoke($blueprint, $original, 10);
            $this->assertLessThanOrEqual(10, strlen($shortened), "El identificador acortado '$shortened' excede la longitud máxima");
            
            if (strlen($original) <= 10) {
                $this->assertEquals($original, $shortened, "El identificador corto no debería modificarse");
            } else {
                $this->assertNotEquals($original, $shortened, "El identificador largo debería acortarse");
            }
        }
        
        // Probar diferentes longitudes máximas
        $longIdentifier = 'columna_extremadamente_larga_que_definitivamente_debe_ser_acortada';
        
        $shortened5 = $method->invoke($blueprint, $longIdentifier, 5);
        $this->assertLessThanOrEqual(5, strlen($shortened5));
        
        $shortened15 = $method->invoke($blueprint, $longIdentifier, 15);
        $this->assertLessThanOrEqual(15, strlen($shortened15));
        
        $shortened30 = $method->invoke($blueprint, $longIdentifier, 30);
        $this->assertLessThanOrEqual(30, strlen($shortened30));
    }
    
    /**
     * Prueba la creación de nombres de claves foráneas con límite de longitud
     */
    public function testCreateForeignKeyName()
    {
        // Crear una instancia de Blueprint para probar el método protegido usando reflexión
        $blueprint = new Blueprint('test_table');
        $reflectionClass = new \ReflectionClass(Blueprint::class);
        
        // Hacer accesible el método protegido createForeignKeyName
        $method = $reflectionClass->getMethod('createForeignKeyName');
        $method->setAccessible(true);
        
        $testCases = [
            // Caso: nombres cortos (no deberían modificarse significativamente)
            [
                'table' => 'users', 
                'foreignTable' => 'roles', 
                'columns' => ['role_id'],
                'maxLength' => 64
            ],
            // Caso: nombres medianos
            [
                'table' => 'user_profiles', 
                'foreignTable' => 'organizations', 
                'columns' => ['organization_id'],
                'maxLength' => 64
            ],
            // Caso: nombres largos
            [
                'table' => 'organization_department_employee_assignments', 
                'foreignTable' => 'employee_position_history_records', 
                'columns' => ['employee_position_id', 'assignment_reference_code'],
                'maxLength' => 64
            ],
            // Caso extremo: nombres muy largos y múltiples columnas
            [
                'table' => 'international_organization_department_employee_assignments_with_very_long_name', 
                'foreignTable' => 'employee_historical_position_department_reference_records_extended', 
                'columns' => [
                    'employee_position_historical_id', 
                    'assignment_reference_code_extended',
                    'international_department_code',
                    'additional_verification_token'
                ],
                'maxLength' => 64
            ],
        ];
        
        foreach ($testCases as $case) {
            $foreignKeyName = $method->invokeArgs(
                $blueprint, 
                [
                    $case['table'], 
                    $case['foreignTable'], 
                    $case['columns'],
                    $case['maxLength']
                ]
            );
            
            // Verificar longitud máxima
            $this->assertLessThanOrEqual(
                $case['maxLength'], 
                strlen($foreignKeyName), 
                "El nombre de clave foránea debería ser menor o igual a {$case['maxLength']} caracteres"
            );
            
            // Verificar que el nombre comienza con fk_
            $this->assertStringStartsWith(
                'fk_', 
                $foreignKeyName, 
                "El nombre de clave foránea debería comenzar con 'fk_'"
            );
            
            // echo "Original: table={$case['table']}, foreignTable={$case['foreignTable']}, columns=" . 
            //      implode(',', $case['columns']) . "\n";
            // echo "Generated FK name: $foreignKeyName (" . strlen($foreignKeyName) . " chars)\n\n";
        }
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
