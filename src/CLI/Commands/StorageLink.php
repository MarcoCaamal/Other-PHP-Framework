<?php

namespace LightWeight\CLI\Commands;

use LightWeight\App;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

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
            
            if (is_link($linkPath)) {
                unlink($linkPath);
            } else {
                $output->writeln("<error>The [public/{$storageUri}] path exists but is not a symbolic link.</error>");
                $output->writeln("Remove the directory manually and try again, or use a different URI in your configuration.");
                return Command::FAILURE;
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
        
        if (@symlink($storagePath, $linkPath)) {
            $output->writeln("<info>The [public/{$storageUri}] link has been created.</info>");
            $relativePath = getRelativePath($linkPath, $storagePath);
            $output->writeln("Link: {$linkPath} -> {$relativePath}");
            return Command::SUCCESS;
        } else {
            $error = error_get_last()['message'] ?? 'Unknown error';
            $output->writeln("<error>Failed to create symbolic link: {$error}</error>");
            
            // Add more diagnostic information
            $output->writeln("");
            $output->writeln("Diagnostics:");
            $output->writeln(" - Public path: {$publicPath} (exists: " . (is_dir($publicPath) ? 'Yes' : 'No') . ")");
            $output->writeln(" - Storage path: {$storagePath} (exists: " . (is_dir($storagePath) ? 'Yes' : 'No') . ")");
            $output->writeln(" - Link path: {$linkPath}");
            
            // Suggest alternate solutions if symlink creation fails
            $output->writeln("");
            $output->writeln("Suggestions:");
            $output->writeln(" - Make sure your web server has write permissions to the public directory");
            $output->writeln(" - On Windows, you may need to run as Administrator or enable Developer Mode");
            $output->writeln(" - Alternatively, you can manually create a directory at [public/{$storageUri}]");
            $output->writeln("   and configure your web server to rewrite storage URLs to your application");
            
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
    // Convert paths to absolute, clean versions
    $from = realpath($from);
    $to = realpath($to);
    
    if ($from === false || $to === false) {
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
