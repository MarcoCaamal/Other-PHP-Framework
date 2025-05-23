<?php

namespace LightWeight\Tests\CLI\Commands;

use LightWeight\Application;
use LightWeight\CLI\Commands\StorageLink;
use LightWeight\Config\Config;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Command\Command;

// Definir PHP_OS_FAMILY si no está disponible (PHP < 7.2)
if (!defined('PHP_OS_FAMILY')) {
    define('PHP_OS_FAMILY', PHP_OS);
}

class StorageLinkTest extends TestCase
{
    protected $tempStoragePath;
    protected $tempPublicPath;
    protected $testStorageUri;

    protected function setUp(): void
    {
        // Crear directorios temporales para las pruebas
        $this->tempStoragePath = __DIR__ . '/temp-storage';
        $this->tempPublicPath = __DIR__ . '/temp-public';
        $this->testStorageUri = 'test-uploads';
        Application::$root  = __DIR__;
        Config::$config['storage.drivers.public.storage_uri'] = $this->testStorageUri;
        Config::$config['storage.storage_uri'] = $this->testStorageUri;

        // Asignar las variables globales
        $GLOBALS['tempStoragePath'] = $this->tempStoragePath;
        $GLOBALS['tempPublicPath'] = $this->tempPublicPath;
        $GLOBALS['testStorageUri'] = $this->testStorageUri;

        if (!is_dir($this->tempStoragePath)) {
            mkdir($this->tempStoragePath, 0755, true);
        }

        if (!is_dir($this->tempPublicPath)) {
            mkdir($this->tempPublicPath, 0755, true);
        }

        // Definir la función publicPath para las pruebas
        if (!function_exists('publicPath')) {
            function publicPath()
            {
                return $GLOBALS['tempPublicPath'];
            }
        }

        // Sobrescribir la función storagePath para las pruebas
        if (!function_exists('storagePath')) {
            function storagePath($path = '')
            {
                $basePath = $GLOBALS['tempStoragePath'];
                return $basePath . ($path ? '/' . ltrim($path, '/') : '');
            }
        }

        // Mock de la función config
        if (!function_exists('config')) {
            function config($key, $default = null)
            {
                if ($key === 'storage.drivers.public.storage_uri' || $key === 'storage.storage_uri') {
                    return $GLOBALS['testStorageUri'];
                }
                return $default;
            }
        }
    }    protected function tearDown(): void
    {
        // Asegurarse de eliminar primero cualquier enlace simbólico
        $linkPath = $this->tempPublicPath . '/' . $this->testStorageUri;

        if (file_exists($linkPath)) {
            // En Windows, a veces hay problemas para detectar si es un enlace simbólico
            $isLink = is_link($linkPath);
            $isDir = is_dir($linkPath);

            if ($isLink) {
                // Si es un enlace simbólico, eliminarlo
                $success = @unlink($linkPath);
                if (!$success) {
                    // Si falla, usar comandos del sistema
                    if (PHP_OS_FAMILY === 'Windows') {
                        @exec('rmdir "' . str_replace('/', '\\', $linkPath) . '" /q');
                    } else {
                        @exec('rm -f "' . $linkPath . '"');
                    }
                }
            } elseif ($isDir) {
                // Si es un directorio, eliminarlo recursivamente
                $this->deleteDirectory($linkPath);
            } else {
                // Si es un archivo regular, eliminarlo
                @unlink($linkPath);
            }

            // Verificar que realmente se eliminó
            clearstatcache(true, $linkPath);
            if (file_exists($linkPath)) {
                // Como último recurso, intentar forzar la eliminación con métodos del sistema
                if (PHP_OS_FAMILY === 'Windows') {
                    @exec('rmdir /s /q "' . str_replace('/', '\\', $linkPath) . '"');
                } else {
                    @exec('rm -rf "' . $linkPath . '"');
                }
            }
        }

        // Dar tiempo al sistema para liberar recursos
        usleep(100000); // 100ms

        // Limpiar directorios temporales
        if (file_exists($this->tempStoragePath)) {
            $this->deleteDirectory($this->tempStoragePath);
        }

        if (file_exists($this->tempPublicPath)) {
            $this->deleteDirectory($this->tempPublicPath);
        }
    }

    /**
     * Método auxiliar para obtener diagnóstico sobre enlaces simbólicos
     */
    protected function getLinkDiagnostics($linkPath, $storagePath)
    {
        $diagnostics = [];
        $diagnostics[] = "Diagnostics:";
        $diagnostics[] = " - Public path: {$this->tempPublicPath} (exists: " . (is_dir($this->tempPublicPath) ? 'Yes' : 'No') . ")";
        $diagnostics[] = " - Storage path: {$storagePath} (exists: " . (is_dir($storagePath) ? 'Yes' : 'No') . ")";
        $diagnostics[] = " - Link path: {$linkPath}";

        $diagnostics[] = "\nSuggestions:";
        $diagnostics[] = " - Make sure your web server has write permissions to the public directory";
        $diagnostics[] = " - On Windows, you may need to run as Administrator or enable Developer Mode";
        $diagnostics[] = " - Alternatively, you can manually create a directory at [public/{$this->testStorageUri}]";
        $diagnostics[] = "   and configure your web server to rewrite storage URLs to your application";

        $diagnostics[] = "\nLink path: {$linkPath}";
        $diagnostics[] = "Storage path: {$storagePath}";
        $diagnostics[] = "Link exists: " . (file_exists($linkPath) ? 'Yes' : 'No');
        $diagnostics[] = "Public path exists: " . (is_dir(dirname($linkPath)) ? 'Yes' : 'No');
        $diagnostics[] = "Storage path exists: " . (is_dir($storagePath) ? 'Yes' : 'No');

        return implode("\n", $diagnostics);
    }

    protected function deleteDirectory($dir)
    {
        if (!file_exists($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = "$dir/$file";

            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }    /**
     * Método alternativo para simular enlaces simbólicos en Windows
     * Crea ya sea un enlace simbólico (si hay permisos) o un directorio normal
     */
    protected function createSymbolicLinkOrDirectory($target, $link)
    {
        // Intentar crear un enlace simbólico con la función nativa de PHP
        $result = @symlink($target, $link);

        // Si falla y estamos en Windows, intentamos usar mklink
        if (!$result && PHP_OS_FAMILY === 'Windows') {
            // Primero, asegúrate de que el directorio padre exista
            $parentDir = dirname($link);
            if (!is_dir($parentDir)) {
                mkdir($parentDir, 0755, true);
            }

            // Intentar con mklink (comando nativo de Windows)
            if (is_dir($target)) {
                $command = 'mklink /D "' . str_replace('/', '\\', $link) . '" "' . str_replace('/', '\\', $target) . '"';
            } else {
                $command = 'mklink "' . str_replace('/', '\\', $link) . '" "' . str_replace('/', '\\', $target) . '"';
            }

            // Ejecutar el comando silenciosamente (sin mostrar la ventana CMD)
            $output = [];
            $returnCode = 0;
            @exec($command . ' 2>&1', $output, $returnCode);

            // Si mklink falla, creamos un directorio simulado como último recurso
            if ($returnCode !== 0) {
                // Crear un directorio en su lugar
                if (!is_dir($link)) {
                    mkdir($link, 0755, true);
                }

                // Crear un archivo .linkinfo que indique el target original
                $linkInfoFile = $link . '/.linkinfo';
                file_put_contents($linkInfoFile, $target);
            }

            // Verificar si la solución funcionó
            return is_link($link) || is_dir($link);
        }

        return $result;
    }
    /**
     * Comprueba si un enlace es válido, ya sea un enlace simbólico real
     * o un directorio simulado con .linkinfo en Windows
     */
    protected function isValidLink($link, $expectedTarget)
    {
        // Normalizar rutas para comparación
        $normalizedExpected = str_replace('\\', '/', $expectedTarget);

        if (is_link($link)) {
            $normalizedActual = str_replace('\\', '/', readlink($link));
            return $normalizedActual === $normalizedExpected;
        } elseif (is_dir($link) && PHP_OS_FAMILY === 'Windows') {
            $linkInfoFile = $link . '/.linkinfo';
            if (file_exists($linkInfoFile)) {
                $normalizedActual = str_replace('\\', '/', trim(file_get_contents($linkInfoFile)));
                return $normalizedActual === $normalizedExpected;
            }
        }

        return false;
    }
    public function testCreateSymbolicLink()
    {
        $command = new StorageLink();
        $input = new ArrayInput([]);
        $output = new BufferedOutput();

        // Crear directorio de almacenamiento público
        $storagePath = $this->tempStoragePath . '/app/public';
        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0755, true);
        }

        // Asegurarse de que no exista el enlace simbólico
        $linkPath = $this->tempPublicPath . '/' . $this->testStorageUri;
        if (file_exists($linkPath)) {
            if (is_link($linkPath)) {
                unlink($linkPath);
            } elseif (is_dir($linkPath)) {
                $this->deleteDirectory($linkPath);
            } else {
                unlink($linkPath);
            }
        }

        // Verificar que realmente se eliminó el enlace
        if (file_exists($linkPath)) {
            $this->fail("No se pudo eliminar el enlace/directorio existente en: $linkPath");
        }

        // Ejecutar el comando
        $result = $command->run($input, $output);

        // Verificar resultado
        $outputContent = $output->fetch();

        // Imprimir información de diagnóstico en caso de fallo
        if ($result !== Command::SUCCESS) {
            echo "\nOutput del comando: " . $outputContent . "\n";
            echo $this->getLinkDiagnostics($linkPath, $storagePath);

            // Detectar si falló porque el enlace ya existía
            if (strpos($outputContent, "link already exists") !== false) {
                $this->fail("El test falló porque el enlace ya existía. Esto no debería ocurrir si tearDown funcionó correctamente.");
            }

            // Si falla debido a permisos en Windows, intentamos crear manualmente
            if (strpos($outputContent, 'Permission denied') !== false && PHP_OS_FAMILY === 'Windows') {
                // Intenta crear el enlace o directorio simulado
                $this->createSymbolicLinkOrDirectory($storagePath, $linkPath);

                // Verifica si nuestra solución alternativa funcionó
                if ($this->isValidLink($linkPath, $storagePath)) {
                    $this->markTestSkipped(
                        'La prueba se omitió en Windows porque no tenemos permisos para crear enlaces simbólicos, ' .
                        'pero se ha creado una alternativa para simular el funcionamiento.'
                    );
                }
            }
        }
        // En Windows, si hay un error de permisos, ajustamos las expectativas
        if (strpos($outputContent, 'Permission denied') !== false && PHP_OS_FAMILY === 'Windows') {
            $this->assertStringContainsString("Failed to create symbolic link", $outputContent);
            $this->assertStringContainsString("Permission denied", $outputContent);
            // La prueba pasa si detectamos el mensaje correcto de error de permisos
        } else {
            $this->assertEquals(Command::SUCCESS, $result);
            $this->assertStringContainsString("The [public/{$this->testStorageUri}] link has been created", $outputContent);
            $this->assertTrue(is_link($linkPath) || $this->isValidLink($linkPath, $storagePath));

            if (is_link($linkPath)) {
                // Normalizar las rutas para comparación (reemplazar barras invertidas con barras normales)
                $normalizedActual = str_replace('\\', '/', readlink($linkPath));
                $normalizedExpected = str_replace('\\', '/', $storagePath);
                $this->assertSame($normalizedExpected, $normalizedActual);
            }
        }
    }

    public function testFailsWhenSymlinkExists()
    {
        $command = new StorageLink();
        $input = new ArrayInput([]);
        $output = new BufferedOutput();

        // Crear directorio de almacenamiento público
        $storagePath = $this->tempStoragePath . '/app/public';
        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0755, true);
        }

        // Crear un enlace simbólico
        $linkPath = $this->tempPublicPath . '/' . $this->testStorageUri;
        // Usamos nuestra función alternativa que funcionará incluso en Windows sin permisos
        $this->createSymbolicLinkOrDirectory($storagePath, $linkPath);

        // Ejecutar el comando
        $result = $command->run($input, $output);

        // Verificar resultado
        $outputContent = $output->fetch();

        // En Windows, solo verificamos que se intentó la operación si hay problemas de permisos
        if (strpos($outputContent, 'Permission denied') !== false && PHP_OS_FAMILY === 'Windows') {
            $this->assertStringContainsString("Permission denied", $outputContent);
            // Test pasa si detectamos el error de permisos específico de Windows
        } else {
            $this->assertEquals(Command::FAILURE, $result);
            $this->assertStringContainsString("The [public/{$this->testStorageUri}] link already exists", $outputContent);
        }
    }

    public function testForceRecreatesSymlink()
    {
        $command = new StorageLink();
        $input = new ArrayInput(['--force' => true]);
        $output = new BufferedOutput();

        // Crear directorio de almacenamiento público
        $storagePath = $this->tempStoragePath . '/app/public';
        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0755, true);
        }

        // Crear un enlace simbólico
        $linkPath = $this->tempPublicPath . '/' . $this->testStorageUri;

        // First ensure the link doesn't exist by removing it if it does
        if (file_exists($linkPath)) {
            if (is_link($linkPath)) {
                @unlink($linkPath);
            } elseif (is_dir($linkPath)) {
                // In Windows, try rmdir for symbolic directory links first
                if (PHP_OS_FAMILY === 'Windows') {
                    @rmdir($linkPath);

                    // If that didn't work, try the command line
                    if (file_exists($linkPath)) {
                        $windowsPath = str_replace('/', '\\', $linkPath);
                        @exec('rmdir /q "' . $windowsPath . '"');

                        // If it still exists, try with /s flag
                        if (file_exists($linkPath)) {
                            @exec('rmdir /s /q "' . $windowsPath . '"');
                        }
                    }
                }

                // If it still exists, use the recursive delete method
                if (file_exists($linkPath)) {
                    $this->deleteDirectory($linkPath);
                }
            } else {
                @unlink($linkPath);
            }

            // Make sure the link is actually gone
            clearstatcache(true, $linkPath);
            if (file_exists($linkPath)) {
                $this->fail("Could not remove the existing link at {$linkPath} before testing --force recreation.");
            }
        }

        // Create the initial symlink that we'll force-recreate
        $created = $this->createSymbolicLinkOrDirectory($storagePath, $linkPath);
        if (!$created) {
            $this->markTestSkipped("Could not create the initial symlink for testing --force option.");
        }

        // Short delay to ensure file system operations complete
        usleep(100000); // 100ms

        // Ejecutar el comando con --force para recrear el enlace
        $result = $command->run($input, $output);

        // Verificar resultado
        $outputContent = $output->fetch();

        // Print debugging information if the test fails
        if ($result !== Command::SUCCESS) {
            echo "\nCommand output: " . $outputContent . "\n";
            echo $this->getLinkDiagnostics($linkPath, $storagePath);
        }

        // En Windows, solo verificamos que se intentó la operación si hay problemas de permisos
        if (strpos($outputContent, 'Permission denied') !== false && PHP_OS_FAMILY === 'Windows') {
            $this->assertStringContainsString("Permission denied", $outputContent);
            // Test pasa si detectamos el error de permisos específico de Windows
        } else {
            $this->assertEquals(Command::SUCCESS, $result, "Command failed with output: " . $outputContent);
            $this->assertStringContainsString("The [public/{$this->testStorageUri}] link has been created", $outputContent);
            $this->assertTrue(is_link($linkPath) || $this->isValidLink($linkPath, $storagePath));

            if (is_link($linkPath)) {
                // Normalizar las rutas para comparación (reemplazar barras invertidas con barras normales)
                $normalizedActual = str_replace('\\', '/', readlink($linkPath));
                $normalizedExpected = str_replace('\\', '/', $storagePath);
                $this->assertSame($normalizedExpected, $normalizedActual);
            }
        }
    }
}
