<?php

namespace LightWeight\CLI\Commands;

use LightWeight\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

// Definir PHP_OS_FAMILY si no está disponible (PHP < 7.2)
if (!defined('PHP_OS_FAMILY')) {
    $os = PHP_OS;
    if (stripos($os, 'WIN') === 0) {
        define('PHP_OS_FAMILY', 'Windows');
    } elseif (stripos($os, 'DARWIN') === 0) {
        define('PHP_OS_FAMILY', 'Darwin');
    } elseif (stripos($os, 'LINUX') === 0) {
        define('PHP_OS_FAMILY', 'Linux');
    } else {
        define('PHP_OS_FAMILY', $os);
    }
}

class StorageLink extends Command
{
    protected static $defaultName = "storage:link";
    protected static $defaultDescription = "Create a symbolic link from public directory to storage/app/public";
    
    protected function configure()
    {
        $this->addOption(
            'force', 
            'f', 
            InputOption::VALUE_NONE, 
            'Force the operation to run when the target already exists'
        );
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Detect if we're in a test context to use the appropriate paths
        $isTestEnv = defined('PHPUNIT_COMPOSER_INSTALL') || defined('__PHPUNIT_PHAR__') || isset($GLOBALS['tempPublicPath']);
        
        if ($isTestEnv && isset($GLOBALS['tempPublicPath']) && isset($GLOBALS['tempStoragePath']) && isset($GLOBALS['testStorageUri'])) {
            $publicPath = $GLOBALS['tempPublicPath'];
            $storagePath = $GLOBALS['tempStoragePath'] . '/app/public';
            $storageUri = $GLOBALS['testStorageUri'];
        } else {
            $publicPath = publicPath();
            $storagePath = storagePath('app/public');
            $storageUri = config('storage.drivers.public.storage_uri', config('storage.storage_uri', 'uploads'));
        }
        
        $linkPath = $publicPath . '/' . $storageUri;
        
        // Verify storage directory exists
        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0755, true);
            $output->writeln("<info>Created storage directory: {$storagePath}</info>");
        }
        
        // Verify public directory exists
        if (!is_dir($publicPath)) {
            mkdir($publicPath, 0755, true);
            $output->writeln("<info>Created public directory: {$publicPath}</info>");
        }
        
        // Check if link already exists
        if (file_exists($linkPath)) {
            if (!$input->getOption('force')) {
                $output->writeln("<error>The [public/{$storageUri}] link already exists.</error>");
                $output->writeln("Use the --force option to recreate the link.");
                return Command::FAILURE;
            }
            
            // Add more aggressive removal for Windows when using --force
            if (PHP_OS_FAMILY === 'Windows') {
                if (is_link($linkPath) || is_dir($linkPath)) {
                    // Try multiple methods to ensure the link is removed
                    $removed = false;
                    
                    // Try PHP's unlink first
                    if (is_link($linkPath)) {
                        $removed = @unlink($linkPath);
                    }
                    
                    // If that didn't work, try rmdir for directory junctions
                    if (!$removed && is_dir($linkPath)) {
                        $removed = @rmdir($linkPath);
                    }
                    
                    // If still not removed, try Windows commands
                    if (!$removed) {
                        // Convert paths to Windows format
                        $windowsPath = str_replace('/', '\\', $linkPath);
                        
                        // Try rmdir for removing directory/junction
                        $command = 'rmdir /q "' . $windowsPath . '"';
                        @exec($command, $execOutput, $returnCode);
                        
                        // If needed, force removal with /s option
                        if (file_exists($linkPath)) {
                            $command = 'rmdir /s /q "' . $windowsPath . '"';
                            @exec($command, $execOutput, $returnCode);
                        }
                        
                        // Clear stat cache to get fresh information about the file
                        clearstatcache(true, $linkPath);
                        
                        if (file_exists($linkPath)) {
                            $output->writeln("<error>Could not remove existing link at {$linkPath}.</error>");
                            $output->writeln("Please remove it manually and try again.");
                            return Command::FAILURE;
                        }
                    }
                } else {
                    // Not a link or directory, try to remove as a file
                    @unlink($linkPath);
                }
            } else {
                // Non-Windows platforms
                if (is_link($linkPath)) {
                    unlink($linkPath);
                } else {
                    $output->writeln("<error>The [public/{$storageUri}] path exists but is not a symbolic link.</error>");
                    $output->writeln("Remove the directory manually and try again, or use a different URI in your configuration.");
                    return Command::FAILURE;
                }
            }
        }
        
        // Create the link
        if (!is_dir(dirname($linkPath))) {
            mkdir(dirname($linkPath), 0755, true);
        }
          // Double check storage directory exists before creating symlink
        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0755, true);
            $output->writeln("<info>Created storage directory: {$storagePath}</info>");
        }
        
        // Intenta crear el enlace simbólico con la función nativa de PHP
        $linkCreated = false;
        
        // Primero intentamos con la función symlink() nativa de PHP
        if (@symlink($storagePath, $linkPath)) {
            $linkCreated = true;
        } 
        // Si falla y estamos en Windows, intentamos con mklink
        elseif (PHP_OS_FAMILY === 'Windows') {
            $output->writeln("<comment>Symlink creation failed. Trying with Windows mklink command...</comment>");
            
            // Preparar el comando mklink con rutas en formato Windows
            $targetPath = str_replace('/', '\\', $storagePath);
            $linkWindowsPath = str_replace('/', '\\', $linkPath);
            
            // Usar /D para directorios
            $command = 'mklink /D "' . $linkWindowsPath . '" "' . $targetPath . '"';
            
            // Ejecutar el comando
            $output->writeln("<comment>Executing: {$command}</comment>");
            $execOutput = [];
            $returnCode = 0;
            @exec($command . ' 2>&1', $execOutput, $returnCode);
            
            if ($returnCode === 0) {
                $linkCreated = true;
                $output->writeln("<info>Successfully created link using mklink command.</info>");
            } else {
                $output->writeln("<error>mklink command failed with code {$returnCode}:</error>");
                foreach ($execOutput as $line) {
                    $output->writeln("  " . $line);
                }
            }
        }
        
        if ($linkCreated) {
            $output->writeln("<info>The [public/{$storageUri}] link has been created.</info>");
            $relativePath = getRelativePath($linkPath, $storagePath);
            $output->writeln("Link: {$linkPath} -> {$relativePath}");
            return Command::SUCCESS;
        } else {
            $error = error_get_last()['message'] ?? 'Unknown error';
            $output->writeln("<error>Failed to create symbolic link: {$error}</error>");
            
            // Add more diagnostic information            $output->writeln("");
            $output->writeln("Diagnostics:");
            $output->writeln(" - Public path: {$publicPath} (exists: " . (is_dir($publicPath) ? 'Yes' : 'No') . ")");
            $output->writeln(" - Storage path: {$storagePath} (exists: " . (is_dir($storagePath) ? 'Yes' : 'No') . ")");
            $output->writeln(" - Link path: {$linkPath}");
            
            // Sugerir crear un directorio alternativo y un archivo .htaccess para redireccionamiento
            $output->writeln("");
            $output->writeln("Suggestions:");
            $output->writeln(" - Make sure your web server has write permissions to the public directory");
            $output->writeln(" - On Windows, you may need to run as Administrator or enable Developer Mode");
            
            // Opción para crear una alternativa a los enlaces simbólicos
            if (PHP_OS_FAMILY === 'Windows' && $input->getOption('force')) {
                $output->writeln("");
                $output->writeln("<comment>Creating an alternative solution for Windows...</comment>");
                
                // Crear directorio si no existe
                if (!is_dir($linkPath)) {
                    mkdir($linkPath, 0755, true);
                }
                
                // Crear un archivo .htaccess para redirigir las solicitudes si es Apache
                $htaccessContent = <<<EOT
# Redirect storage requests to the actual storage location
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ {$storagePath}/$1 [L]
</IfModule>
EOT;
                file_put_contents($linkPath . '/.htaccess', $htaccessContent);
                
                // Crear un archivo info.txt que indique dónde está el almacenamiento real
                $infoContent = <<<EOT
This directory is a fallback for symbolic links.
The actual storage location is: {$storagePath}
EOT;
                file_put_contents($linkPath . '/info.txt', $infoContent);
                
                $output->writeln("<info>Created alternative directory structure.</info>");
                $output->writeln("<comment>Note: This is not a real symbolic link. Some functionality may be limited.</comment>");
                $output->writeln("<comment>If you're using Apache, the included .htaccess file should redirect requests.</comment>");
                
                // Devolver SUCCESS, ya que hemos creado una solución alternativa
                return Command::SUCCESS;
            }
            
            $output->writeln(" - Alternatively, you can manually create a directory at [public/{$storageUri}]");
            $output->writeln("   and configure your web server to rewrite storage URLs to your application");
            $output->writeln(" - Use --force option to automatically create an alternative directory structure");
            
            return Command::FAILURE;
        }
    }
}

/**
 * Get the relative path from one directory to another
 *
 * @param string $from
 * @param string $to
 * @return string
 */
function getRelativePath($from, $to)
{
    // Normalizar rutas (convertir barras invertidas a barras normales)
    $from = str_replace('\\', '/', realpath($from) ?: $from);
    $to = str_replace('\\', '/', realpath($to) ?: $to);
    
    if (empty($from) || empty($to)) {
        return $to;
    }
    
    // Split paths into arrays
    $fromParts = explode('/', $from);
    $toParts = explode('/', $to);
    
    // Find common path
    $length = min(count($fromParts), count($toParts));
    $commonLength = 0;
    
    for ($i = 0; $i < $length; $i++) {
        if ($fromParts[$i] === $toParts[$i]) {
            $commonLength = $i + 1;
        } else {
            break;
        }
    }
    
    // Calculate relative path
    $relativePath = str_repeat('../', count($fromParts) - $commonLength);
    
    for ($i = $commonLength; $i < count($toParts); $i++) {
        $relativePath .= $toParts[$i] . '/';
    }
    
    return rtrim($relativePath, '/');
}
