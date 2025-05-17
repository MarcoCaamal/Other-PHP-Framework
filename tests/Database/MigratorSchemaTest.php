<?php

namespace LightWeight\Tests\Database;

use PHPUnit\Framework\TestCase;
use LightWeight\Database\Migrations\Migrator;
use LightWeight\Database\Contracts\DatabaseDriverContract;
use PHPUnit\Framework\Attributes\DataProvider;

class MigratorSchemaTest extends TestCase
{
    use RefreshDatabase {
        setUp as protected dbSetUp;
        tearDown as protected dbTearDown;
    }
    
    protected ?DatabaseDriverContract $driver = null;
    protected $templatesDirectory = __DIR__ . "/templates";
    protected $migrationsDirectory = __DIR__ . "/migrations";
    protected Migrator $migrator;
    
    protected function setUp(): void
    {
        if (!file_exists($this->migrationsDirectory)) {
            mkdir($this->migrationsDirectory);
        }
        
        // Asegurarse de que existe la plantilla de migración para pruebas
        if (!file_exists("$this->templatesDirectory/migration.template")) {
            copy(
                "/home/marco/public_html/LightWeight/templates/migration.template",
                "$this->templatesDirectory/migration.template"
            );
        }
        
        $this->dbSetUp();
        $this->migrator = new Migrator(
            $this->migrationsDirectory,
            $this->templatesDirectory,
            $this->driver,
            false
        );
        $this->migrator->createMigrationsTableIfNotExists();
    }
    
    protected function tearDown(): void
    {
        $files = glob($this->migrationsDirectory . '/*'); // Obtiene todos los archivos en el directorio
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        $this->dbTearDown();
    }
    
    /**
     * Proveedor de datos para las migraciones con opciones de campo
     */
    public static function fieldOptionsProvider()
    {
        return [
            // [Nombre de migración, opciones, columnas a verificar]
            [
                'create_users_table',
                [
                    'fields' => [
                        ['name' => 'name', 'type' => 'string', 'parameters' => ['100']],
                        ['name' => 'email', 'type' => 'string'],
                        ['name' => 'age', 'type' => 'integer'],
                        ['name' => 'is_active', 'type' => 'boolean']
                    ]
                ],
                ['id', 'name', 'email', 'age', 'is_active']
            ],
            [
                'create_products_table',
                [
                    'fields' => [
                        ['name' => 'name', 'type' => 'string'],
                        ['name' => 'price', 'type' => 'decimal', 'parameters' => ['10', '2']],
                        ['name' => 'description', 'type' => 'text'],
                        ['name' => 'category', 'type' => 'enum', 'parameters' => ['electronics', 'books', 'clothing']]
                    ]
                ],
                ['id', 'name', 'price', 'description', 'category']
            ],
            [
                'create_orders_table',
                [
                    'fields' => [
                        ['name' => 'user_id', 'type' => 'integer'],
                        ['name' => 'total', 'type' => 'decimal', 'parameters' => ['8', '2']],
                        ['name' => 'status', 'type' => 'string'],
                        ['name' => 'order_date', 'type' => 'datetime']
                    ]
                ],
                ['id', 'user_id', 'total', 'status', 'order_date']
            ]
        ];
    }
    
    #[DataProvider('fieldOptionsProvider')]
    public function testMigrationWithFieldOptions(string $migrationName, array $options, array $expectedColumns)
    {
        // Crear la migración con opciones de campo
        $fileName = $this->migrator->make($migrationName, $options);
        
        // Ejecutar la migración
        $this->migrator->migrate();
        
        // Extraer el nombre de tabla del nombre de la migración
        preg_match("/create_(.*)_table/", $migrationName, $matches);
        $tableName = $matches[1];
        
        // Verificar que la tabla existe con las columnas especificadas
        $columns = $this->driver->statement("SHOW COLUMNS FROM $tableName");
        $columnNames = array_column($columns, 'Field');
        
        foreach ($expectedColumns as $columnName) {
            $this->assertContains($columnName, $columnNames, "La columna '$columnName' no se encontró en la tabla");
        }
    }
    
    public function testAddColumnsToExistingTable()
    {
        // 1. Crear tabla inicial
        $this->migrator->make('create_customers_table');
        $this->migrator->migrate();
        
        // Verificar estado inicial
        $initialColumns = $this->driver->statement("SHOW COLUMNS FROM customers");
        $initialColumnCount = count($initialColumns);
        
        // 2. Añadir columnas a la tabla existente
        $this->migrator->make('add_address_to_customers_table', [
            'fields' => [
                ['name' => 'address', 'type' => 'string'],
                ['name' => 'city', 'type' => 'string'],
                ['name' => 'postal_code', 'type' => 'string']
            ]
        ]);
        
        $this->migrator->migrate();
        
        // Verificar que se añadieron las columnas
        $updatedColumns = $this->driver->statement("SHOW COLUMNS FROM customers");
        $updatedColumnNames = array_column($updatedColumns, 'Field');
        
        $this->assertEquals($initialColumnCount + 3, count($updatedColumns));
        $this->assertContains('address', $updatedColumnNames);
        $this->assertContains('city', $updatedColumnNames);
        $this->assertContains('postal_code', $updatedColumnNames);
    }
    
    public function testRemoveColumnsFromExistingTable()
    {
        // 1. Crear tabla con columnas específicas
        $this->migrator->make('create_employees_table', [
            'fields' => [
                ['name' => 'name', 'type' => 'string'],
                ['name' => 'position', 'type' => 'string'],
                ['name' => 'department', 'type' => 'string'],
                ['name' => 'salary', 'type' => 'decimal', 'parameters' => ['10', '2']],
                ['name' => 'notes', 'type' => 'text']
            ]
        ]);
        $this->migrator->migrate();
        
        // Verificar estado inicial
        $initialColumns = $this->driver->statement("SHOW COLUMNS FROM employees");
        $initialColumnCount = count($initialColumns);
        
        // 2. Eliminar columnas
        $this->migrator->make('remove_notes_from_employees_table', [
            'fields' => [
                ['name' => 'notes', 'type' => 'text']
            ]
        ]);
        $this->migrator->migrate();
        
        // Verificar que se eliminó la columna
        $updatedColumns = $this->driver->statement("SHOW COLUMNS FROM employees");
        $updatedColumnNames = array_column($updatedColumns, 'Field');
        
        $this->assertEquals($initialColumnCount - 1, count($updatedColumns));
        $this->assertNotContains('notes', $updatedColumnNames);
        $this->assertContains('name', $updatedColumnNames);
        $this->assertContains('position', $updatedColumnNames);
    }
    
    public function testMigrationRollbackRestoresColumns()
    {
        // 1. Crear tabla inicial
        $initialMigration = $this->migrator->make('create_projects_table', [
            'fields' => [
                ['name' => 'name', 'type' => 'string'],
                ['name' => 'status', 'type' => 'string']
            ]
        ]);
        $this->migrator->migrate();
        
        // 2. Añadir columnas adicionales
        $addColumnMigration = $this->migrator->make('add_deadline_to_projects_table', [
            'fields' => [
                ['name' => 'deadline', 'type' => 'date'],
                ['name' => 'priority', 'type' => 'integer']
            ]
        ]);
        $this->migrator->migrate();
        
        // Verificar que las columnas existen
        $columnsAfterAddition = $this->driver->statement("SHOW COLUMNS FROM projects");
        $columnNamesAfterAddition = array_column($columnsAfterAddition, 'Field');
        $this->assertContains('deadline', $columnNamesAfterAddition);
        $this->assertContains('priority', $columnNamesAfterAddition);
        
        // 3. Realizar rollback
        $this->migrator->rollback(1);
        
        // Verificar que las columnas añadidas fueron eliminadas
        $columnsAfterRollback = $this->driver->statement("SHOW COLUMNS FROM projects");
        $columnNamesAfterRollback = array_column($columnsAfterRollback, 'Field');
        $this->assertNotContains('deadline', $columnNamesAfterRollback);
        $this->assertNotContains('priority', $columnNamesAfterRollback);
    }
    
    public function testComplexSchemaWithForeignKeys()
    {
        // 1. Crear tabla de fabricantes con una migración normal
        $manufacturerMigration = $this->migrator->make('create_manufacturers_table', [
            'fields' => [
                ['name' => 'name', 'type' => 'string']
            ]
        ]);
        $this->migrator->migrate();
        
        // 2. Crear tabla de productos electrónicos con clave foránea
        $electronicsFile = $this->migrator->make('create_electronics_table', [
            'fields' => [
                ['name' => 'name', 'type' => 'string'],
                ['name' => 'price', 'type' => 'decimal', 'parameters' => ['10', '2']],
                ['name' => 'manufacturer_id', 'type' => 'integer']
            ]
        ]);
        
        // Modificar el contenido del archivo para añadir la clave foránea
        $migrations = glob($this->migrationsDirectory . '/*_create_electronics_table.php');
        if (count($migrations) > 0) {
            $content = file_get_contents($migrations[0]);
            
            // Buscar y reemplazar para añadir la definición de clave foránea
            $pattern = '/public function up\(\)\s*\{\s*(.*?)Schema::create\([\'"]electronics[\'"], function \(Blueprint \$table\) \{\s*(.*?)\}\);/s';
            $replacement = 'public function up()
    {
        $1Schema::create(\'electronics\', function (Blueprint $table) {
            $2
            // Añadir clave foránea
            $table->foreign(\'manufacturer_id\')->references(\'id\')->on(\'manufacturers\');
        });';
            
            $modifiedContent = preg_replace($pattern, $replacement, $content);
            file_put_contents($migrations[0], $modifiedContent);
        }
        
        // 3. Ejecutar la migración modificada
        $this->migrator->migrate();
        
        // 4. Verificar que la clave foránea se creó correctamente
        $createTableSql = $this->driver->statement("SHOW CREATE TABLE electronics")[0]['Create Table'];
        $this->assertStringContainsString('FOREIGN KEY', $createTableSql);
        $this->assertStringContainsString('`manufacturer_id`', $createTableSql);
        $this->assertStringContainsString('`manufacturers`', $createTableSql);
        
        // 5. Verificar que el rollback funciona correctamente con claves foráneas
        $this->migrator->rollback(1);
        
        // Verificar que la tabla electronics ha sido eliminada
        $tableExists = false;
        try {
            $this->driver->statement("SELECT 1 FROM electronics LIMIT 1");
            $tableExists = true;
        } catch (\Exception $e) {
            // La tabla no existe, lo cual es correcto
        }
        $this->assertFalse($tableExists, "La tabla 'electronics' debería haberse eliminado durante el rollback");
    }
}
