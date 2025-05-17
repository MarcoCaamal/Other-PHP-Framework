<?php

/**
 * Get the application root directory
 * 
 * @return string Application root directory
 */
function app_directory(): string
{
    return \LightWeight\App::$root ?? getcwd();
}

/**
 * Get the resources directory
 * 
 * @return string Resources directory
 */
function resources_directory(): string
{
    return app_directory() . '/resources';
}

/**
 * Get the storage directory
 * 
 * @return string Storage directory
 */
function storage_directory(): string
{
    return app_directory() . '/storage';
}

/**
 * Get the public directory
 * 
 * @return string Public directory
 */
function public_directory(): string
{
    return app_directory() . '/public';
}

/**
 * Get the templates directory
 * 
 * @return string Templates directory
 */
function templates_directory(): string
{
    return app_directory() . '/templates';
}

/**
 * Find a file in multiple possible framework paths
 * 
 * @param string $relativePath Relative path to the file to find
 * @param array $additionalPaths Additional paths to check
 * @return string|null Full path to the file if found, null otherwise
 */
function find_framework_file(string $relativePath, array $additionalPaths = []): ?string
{
    // Start with standard locations
    $paths = [
        // Application directory
        app_directory() . '/' . $relativePath,
        // Framework in development mode
        dirname(dirname(dirname(__FILE__))) . '/' . $relativePath,
        // Framework in vendor directory (Composer installation)
        dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/marco/lightweight/' . $relativePath,
    ];
    
    // Add any additional paths
    foreach ($additionalPaths as $path) {
        $paths[] = $path . '/' . $relativePath;
    }
    
    // Return the first matching path
    foreach ($paths as $path) {
        if (file_exists($path)) {
            return $path;
        }
    }
    
    return null;
}
