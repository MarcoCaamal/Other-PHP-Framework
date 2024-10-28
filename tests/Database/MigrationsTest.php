<?php

namespace SMFramework\Tests\Database;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use SMFramework\Database\Contracts\DatabaseDriverContract;
use SMFramework\Database\Migrations\Migrator;

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
    }
    #[DataProvider('migrationNames')]
    public function testCreatesMigrationFiles($name, $expectedMigrationFile)
    {
        $expectedName = sprintf("%s_%06d_%s.php", date('Y_m_d'), 0, $name);
        $this->migrator->make($name);
        $file = "$this->migrationsDirectory/$expectedName";
        $this->assertFileExists($file);
        $this->assertFileEquals($expectedMigrationFile, $file);
    }
    #[Depends('testCreatesMigrationFiles')]
    public function testMigrateFiles()
    {
        $tables = ["users", "products", "sellers"];
        $migrated = [];
        foreach ($tables as $table) {
            $migrated[] = $this->migrator->make("create_{$table}_table");
        }
        $this->migrator->migrate();
        $rows = $this->driver->statement("SELECT * FROM migrations");
        $this->assertEquals(3, count($rows));
        $this->assertEquals($migrated, array_column($rows, "name"));
        foreach ($tables as $table) {
            try {
                $this->driver->statement("SELECT * FROM $table");
            } catch (\PDOException $e) {
                $this->fail("Failed accessing migrated table $table: {$e->getMessage()}");
            }
        }
    }
}
