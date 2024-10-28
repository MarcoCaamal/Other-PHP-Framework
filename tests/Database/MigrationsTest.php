<?php

namespace SMFramework\Tests\Database;

use PHPUnit\Framework\Attributes\DataProvider;
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
            $this->driver
        );
    }
    protected function tearDown(): void
    {
        // if (is_dir($this->migrationsDirectory)) {
        //     // Abre el directorio
        //     $archivos = scandir($this->migrationsDirectory);

        //     // Itera sobre cada archivo en el directorio
        //     foreach ($archivos as $archivo) {
        //         // Ignora las entradas especiales `.` y `..`
        //         if ($archivo !== '.' && $archivo !== '..') {
        //             // Construye la ruta completa del archivo
        //             $rutaArchivo = $this->migrationsDirectory . '/' . $archivo;

        //             // Verifica que sea un archivo antes de eliminarlo
        //             if (is_file($rutaArchivo)) {
        //                 unlink($rutaArchivo); // Elimina el archivo
        //             }
        //         }
        //     }
        //     echo "Archivos eliminados exitosamente.";
        // } else {
        //     echo "El directorio no existe.";
        // }
        $this->dbTearDown();
    }
    public static function migrationNames()
    {
        return [
            [
                "create_products_table",
                __DIR__ . "/expected" . "/create_products_table.php",
                0
            ],
            [
                "add_category_to_products_table",
                __DIR__ . "/expected" . "/add_category_to_products_table.php",
                1
            ],
            [
                "remove_name_from_products_table",
                __DIR__ . "/expected" . "/remove_name_from_products_table.php",
                2
            ],
        ];
    }
    #[DataProvider('migrationNames')]
    public function testCreatesMigrationFiles($name, $expectedMigrationFile, $id)
    {
        $expectedName = sprintf("%s_%06d_%s.php", date('Y_m_d'), $id, $name);
        $this->migrator->make($name);
        $file = "$this->migrationsDirectory/$expectedName";
        $this->assertFileExists($file);
        $this->assertFileEquals($expectedMigrationFile, $file);
    }
}
