<?php

namespace LightWeight\Tests\CLI\Commands;

use LightWeight\App;
use LightWeight\CLI\Commands\StorageLink;
use LightWeight\Config\Config;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Command\Command;

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
        App::$root  = __DIR__;
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
    }
    
    protected function tearDown(): void
    {
        // Limpiar directorios temporales
        $this->deleteDirectory($this->tempStoragePath);
        $this->deleteDirectory($this->tempPublicPath);
    }
    
    /**
     * Método auxiliar para obtener diagnóstico sobre enlaces simbólicos
     */
    protected function getLinkDiagnostics($linkPath, $storagePath)
    {
        $diagnostics = [];
        $diagnostics[] = "Link path: {$linkPath}";
        $diagnostics[] = "Storage path: {$storagePath}";
        $diagnostics[] = "Link exists: " . (file_exists($linkPath) ? 'Yes' : 'No');
        
        if (file_exists($linkPath)) {
            $diagnostics[] = "Is symbolic link: " . (is_link($linkPath) ? 'Yes' : 'No');
            if (is_link($linkPath)) {
                $diagnostics[] = "Link target: " . readlink($linkPath);
                $diagnostics[] = "Target exists: " . (file_exists(readlink($linkPath)) ? 'Yes' : 'No');
            }
        }
        
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
        
        // Ejecutar el comando
        $result = $command->run($input, $output);
        
        // Verificar resultado
        $outputContent = $output->fetch();
        
        // Imprimir información de diagnóstico en caso de fallo
        if ($result !== Command::SUCCESS) {
            echo "\nOutput del comando: " . $outputContent . "\n";
            echo $this->getLinkDiagnostics($linkPath, $storagePath);
        }
        
        $this->assertEquals(Command::SUCCESS, $result);
        $this->assertStringContainsString("The [public/{$this->testStorageUri}] link has been created", $outputContent);
        $this->assertTrue(is_link($linkPath));
        $this->assertSame($storagePath, readlink($linkPath));
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
        symlink($storagePath, $linkPath);
        
        // Ejecutar el comando
        $result = $command->run($input, $output);
        
        // Verificar resultado
        $outputContent = $output->fetch();
        
        $this->assertEquals(Command::FAILURE, $result);
        $this->assertStringContainsString("The [public/{$this->testStorageUri}] link already exists", $outputContent);
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
        symlink($storagePath, $linkPath);
        
        // Ejecutar el comando
        $result = $command->run($input, $output);
        
        // Verificar resultado
        $outputContent = $output->fetch();
        
        $this->assertEquals(Command::SUCCESS, $result);
        $this->assertStringContainsString("The [public/{$this->testStorageUri}] link has been created", $outputContent);
        $this->assertTrue(is_link($linkPath));
        $this->assertSame($storagePath, readlink($linkPath));
    }
}
