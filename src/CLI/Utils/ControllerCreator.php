<?php

namespace LightWeight\CLI\Utils;

use LightWeight\App;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

class ControllerCreator
{
    // Define available controller types
    protected array $controllerTypes = [
        'basic' => 'controller.template',
        'api' => 'controller.api.template',
        'resource' => 'controller.resource.template',
        'web' => 'controller.web.template',
    ];
    
    /**
     * Create a new controller
     *
     * @param OutputInterface $output Console output interface
     * @param string $name Controller name (can include namespaces)
     * @param string $type Controller type (basic, api, resource, web)
     * @return array Array with [status, controllerName, namespace, baseControllerName]
     */
    public function createController(OutputInterface $output, string $name, string $type): array
    {
        // Convert normal slashes to backslashes for namespaces
        $name = str_replace('/', '\\', $name);
        
        // Separate the namespace and controller name
        $parts = explode('\\', $name);
        $controllerName = array_pop($parts); // Last element is the controller name
        $namespace = implode('\\', $parts); // The rest is the namespace
        
        // Make sure the controller name ends with "Controller"
        if (!str_ends_with($controllerName, 'Controller')) {
            $controllerName .= 'Controller';
        }
        
        // Build the directory path
        $dirPath = App::$root . "/app/Controllers";
        
        // Verify that the base controllers directory exists
        if (!is_dir(App::$root . "/app")) {
            if (!mkdir(App::$root . "/app", 0755, true)) {
                $output->writeln("<error>Could not create base directory: " . App::$root . "/app</error>");
                return [Command::FAILURE, $controllerName, $namespace, null];
            }
        }
        
        if (!is_dir($dirPath)) {
            if (!mkdir($dirPath, 0755, true)) {
                $output->writeln("<error>Could not create directory: $dirPath</error>");
                return [Command::FAILURE, $controllerName, $namespace, null];
            }
        }
        
        if (!empty($namespace)) {
            $dirPath .= '/' . str_replace('\\', '/', $namespace);
            
            // Create subdirectories if they don't exist
            if (!is_dir($dirPath)) {
                if (!mkdir($dirPath, 0755, true)) {
                    $output->writeln("<error>Could not create directory: $dirPath</error>");
                    return [Command::FAILURE, $controllerName, $namespace, null];
                }
            }
        }
        
        // Validate the controller type
        if (!array_key_exists($type, $this->controllerTypes)) {
            $output->writeln("<error>Invalid controller type: $type</error>");
            $output->writeln("Available types: " . implode(", ", array_keys($this->controllerTypes)));
            return [Command::FAILURE, $controllerName, $namespace, null];
        }
        
        // Get the template according to the controller type
        $templateFile = $this->controllerTypes[$type];
        $templatePath = dirname(__DIR__, 3) . "/templates/$templateFile";
        
        if (!file_exists($templatePath)) {
            $output->writeln("<error>Template not found: $templatePath</error>");
            return [Command::FAILURE, $controllerName, $namespace, null];
        }
        
        $template = file_get_contents($templatePath);
        
        // Extract the base name for other resources
        $baseControllerName = str_replace('Controller', '', $controllerName);
        
        // Replace the controller name in the template
        $template = str_replace("ControllerName", $controllerName, $template);
        
        // If it's a web controller, also replace the view name
        if ($type === 'web') {
            $template = str_replace("ControllerNameView", $baseControllerName, $template);
            $template = str_replace("ControllerNameRoute", $this->getRoutePath($baseControllerName), $template);
        }
        
        // If there is a namespace, add it to the template
        if (!empty($namespace)) {
            $fullNamespace = "App\\Controllers\\$namespace";
            $template = str_replace("namespace App\\Controllers;", "namespace $fullNamespace;", $template);
        }
        
        // Full file path
        $filePath = "$dirPath/$controllerName.php";
        
        // Check if the file already exists
        if (file_exists($filePath)) {
            $output->writeln("<error>The controller already exists: $filePath</error>");
            $output->writeln("Use different options or delete the existing file.");
            return [Command::FAILURE, $controllerName, $namespace, $baseControllerName];
        }
        
        // Save the file
        if (!file_put_contents($filePath, $template)) {
            $output->writeln("<error>Could not create the controller: $filePath</error>");
            return [Command::FAILURE, $controllerName, $namespace, $baseControllerName];
        }
        
        $relativePath = str_replace(App::$root, '', $filePath);
        $output->writeln("<info>Controller created => $relativePath</info>");
        
        return [Command::SUCCESS, $controllerName, $namespace, $baseControllerName];
    }
    
    /**
     * Get the table name for a model
     * 
     * @param string $input Model name
     * @return string
     */
    protected function getTableName(string $input): string
    {
        // Use the helper function from helpers/string.php
        return tableName($input);
    }
    
    /**
     * Get the route path for a controller
     * 
     * @param string $controllerName Controller name
     * @return string
     */
    protected function getRoutePath(string $controllerName): string
    {
        // Use the helper function from helpers/string.php
        return routePath($controllerName);
    }
}
