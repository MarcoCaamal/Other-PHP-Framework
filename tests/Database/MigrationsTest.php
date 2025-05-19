<?php

namespace LightWeight\Tests\Database;

use PDOException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use LightWeight\Database\Contracts\DatabaseDriverContract;
use LightWeight\Database\Migrations\Migrator;

class MigrationsTest extends TestCase
{
    use RefreshDatabase {
        setUp as protected dbSetUp;
        tearDown as protected dbTearDown;
    }
    protected ?DatabaseDriverContract $driver = null;
    protected $templatesDirectory = __DIR__ . "/templates";
    protected $migrationsDirectory = __DIR__ . "/migrations";
    protected $expectedMigrations = __DIR__ . "/expected";
    protected Migrator $migrator;
    protected function setUp(): void
    {
        if (!file_exists($this->migrationsDirectory)) {
            mkdir($this->migrationsDirectory);
        }
        
        // Asegurarse de que existe la plantilla de migración para pruebas
        if (!file_exists("$this->templatesDirectory/migration.template")) {
            copy(
                dirname(dirname(__DIR__)) . "/templates/migration.template",
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
    public static function migrationNames()
    {
        return [
            [
                "create_products_table",
                __DIR__ . "/expected" . "/create_products_table.php"
            ],
            [
                "add_category_to_products_table",
                __DIR__ . "/expected" . "/add_category_to_products_table.php"
            ],
            [
                "remove_name_from_products_table",
                __DIR__ . "/expected" . "/remove_name_from_products_table.php",
            ],
        ];
    }    #[DataProvider('migrationNames')]
    public function testCreatesMigrationFiles($name, $expectedMigrationFile)
    {
        $expectedName = sprintf("%s_%06d_%s.php", date('Y_m_d'), 0, $name);
        $this->migrator->make($name);
        $file = "$this->migrationsDirectory/$expectedName";
        $this->assertFileExists($file);
        
        // Normalizar los finales de línea antes de comparar
        $expected = file_get_contents($expectedMigrationFile);
        $actual = file_get_contents($file);
        
        // Convertir todas las terminaciones de línea a \n para la comparación
        $expected = str_replace(["\r\n", "\r"], "\n", $expected);
        $actual = str_replace(["\r\n", "\r"], "\n", $actual);
        
        $this->assertEquals($expected, $actual);
    }
    #[Depends('testCreatesMigrationFiles')]
    public function testMigrateFiles()
    {
        // Crear migraciones para tablas usando el nuevo Schema
        $tables = ["users", "products", "sellers"];
        $migrated = [];
        foreach ($tables as $table) {
            $migrated[] = $this->migrator->make("create_{$table}_table");
        }
        
        // Ejecutar las migraciones
        $this->migrator->migrate();
        
        // Verificar que se registraron las migraciones
        $rows = $this->driver->statement("SELECT * FROM migrations");
        $this->assertEquals(3, count($rows));
        $this->assertEquals($migrated, array_column($rows, "name"));
        
        // Verificar que las tablas fueron creadas
        foreach ($tables as $table) {
            try {
                $this->driver->statement("SELECT * FROM $table");
            } catch (PDOException $e) {
                $this->fail("Failed accessing migrated table $table: {$e->getMessage()}");
            }
        }
        
        // Verificar que cada tabla tiene al menos las columnas id, created_at y updated_at
        foreach ($tables as $table) {
            $columns = $this->driver->statement("SHOW COLUMNS FROM $table");
            $columnNames = array_column($columns, 'Field');
            $this->assertContains('id', $columnNames);
            $this->assertContains('created_at', $columnNames);
            $this->assertContains('updated_at', $columnNames);
        }
    }
    #[Depends('testCreatesMigrationFiles')]
    public function testRollbackFiles()
    {
        // Crear migraciones para varias tablas
        $tables = ["users", "products", "sellers", "providers", "referals"];
        $migrated = [];
        
        foreach ($tables as $table) {
            $migrated[] = $this->migrator->make("create_{$table}_table");
        }
        
        // Ejecutar migraciones
        $this->migrator->migrate();
        
        // Revertir la última migración
        $this->migrator->rollback(1);
        
        // Verificar que se eliminó la última migración
        $rows = $this->driver->statement("SELECT * FROM migrations");
        $this->assertEquals(4, count($rows));
        $this->assertEquals(array_slice($migrated, 0, 4), array_column($rows, "name"));
        
        // Verificar que la tabla fue eliminada
        $lastTable = $tables[count($tables) - 1];
        try {
            $this->driver->statement("SELECT * FROM $lastTable");
            $this->fail("Table $lastTable was not deleted after rolling back");
        } catch (PDOException $e) {
            // OK - Se espera que la tabla no exista
        }
        
        // Revertir otras 2 migraciones
        $this->migrator->rollback(2);
        
        // Verificar que se eliminaron las migraciones
        $rows = $this->driver->statement("SELECT * FROM migrations");
        $this->assertEquals(2, count($rows));
        $this->assertEquals(array_slice($migrated, 0, 2), array_column($rows, "name"));
        
        // Verificar que las tablas fueron eliminadas
        foreach (array_slice($tables, 2, 2) as $table) {
            try {
                $this->driver->statement("SELECT * FROM $table");
                $this->fail("Table '$table' was not deleted after rolling back");
            } catch (PDOException $e) {
                // OK - Se espera que la tabla no exista
            }
        }
        
        // Revertir las migraciones restantes
        $this->migrator->rollback();
        
        // Verificar que todas las migraciones fueron eliminadas
        $rows = $this->driver->statement("SELECT * FROM migrations");
        $this->assertEquals(0, count($rows));
        
        // Verificar que todas las tablas fueron eliminadas
        foreach ($tables as $table) {
            try {
                $this->driver->statement("SELECT * FROM $table");
                $this->fail("Table '$table' was not deleted after rolling back all migrations");
            } catch (PDOException $e) {
                // OK - Se espera que la tabla no exista
            }
        }
    }
}
