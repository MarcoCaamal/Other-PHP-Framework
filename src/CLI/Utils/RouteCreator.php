<?php

namespace LightWeight\CLI\Utils;

use LightWeight\App;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

class RouteCreator
{
    /**
     * Creates or updates route files
     *
     * @param OutputInterface $output
     * @param string $controllerName
     * @param string $namespace
     * @param string $routeFileName The name of the route file to create or update
     * @param string $controllerType The type of controller (api, web, resource)
     * @return int
     */
    public function createRoutes(OutputInterface $output, string $controllerName, string $namespace, string $routeFileName, string $controllerType): int
    {
        $routesDir = App::$root . "/routes";
        
        // Create routes directory if it doesn't exist
        if (!is_dir($routesDir)) {
            if (!mkdir($routesDir, 0755, true)) {
                $output->writeln("<error>Could not create routes directory: $routesDir</error>");
                return Command::FAILURE;
            }
        }
        
        // Determine the route file name based on the provided name or controller type
        if ($routeFileName === 'new') {
            // Create a new file based on controller name if "new" was specified
            $routesFile = "$routesDir/" . camelCase($controllerName) . ".php";
        } else if (empty($routeFileName) || $routeFileName === 'true') {
            // Use controller type as default (web.php, api.php, etc.)
            $routesFile = "$routesDir/" . strtolower($controllerType) . ".php";
        } else {
            // Use the provided route file name
            $routesFile = "$routesDir/" . $routeFileName . ".php";
        }
        
        // Build the controller class name
        $controllerClass = "App\\Controllers";
        if (!empty($namespace)) {
            $controllerClass .= "\\$namespace";
        }
        $controllerClass .= "\\{$controllerName}Controller";
        
        // Get routes template
        $templatePath = dirname(__DIR__, 3) . "/templates/routes.template";

        if (!file_exists($templatePath)) {
            $output->writeln("<error>Routes template not found</error>");
            return Command::FAILURE;
        }
        
        $template = file_get_contents($templatePath);
        
        // Replace values in the template
        $routePath = $this->getRoutePath($controllerName);
        $template = str_replace("ControllerNameRoute", $routePath, $template);
        $template = str_replace("ControllerClass", $controllerClass, $template);
        $template = str_replace("controllerRoute", camelCase($controllerName), $template);
        
        // Extract route lines from template (without PHP tags and use statements)
        $routeLines = [];
        $lines = explode("\n", $template);
        $startCapturing = false;
        
        foreach ($lines as $line) {
            // Skip PHP and use declarations
            if (strpos($line, '<?php') === 0 || strpos($line, 'use ') === 0) {
                continue;
            }
            
            // Start capturing at first route
            if (strpos($line, 'Route::') !== false) {
                $startCapturing = true;
            }
            
            if ($startCapturing) {
                $routeLines[] = $line;
            }
        }
        
        // Create routes header
        $routesHeader = "/*\n|--------------------------------------------------------------------------\n";
        $routesHeader .= "| " . ucfirst($controllerName) . " Routes\n";
        $routesHeader .= "|--------------------------------------------------------------------------\n|\n";
        $routesHeader .= "| Route definitions for the " . $controllerName . " controller.\n|\n*/\n\n";
        $routesHeader .= "// Basic CRUD routes\n";
        
        // Combine routes
        $routes = $routesHeader . implode("\n", $routeLines);
        
        if (!file_exists($routesFile)) {
            // Create new file
            $content = "<?php\n\nuse LightWeight\Routing\Route;\n\n" . $routes;
            
            if (!file_put_contents($routesFile, $content)) {
                $output->writeln("<error>Could not create the routes file: $routesFile</error>");
                return Command::FAILURE;
            }
            
            $relativePath = str_replace(App::$root, '', $routesFile);
            $output->writeln("<info>Routes file created => $relativePath</info>");
        } else {
            // Append to existing file
            $content = file_get_contents($routesFile);
            
            // Add routes at the end with a separator
            $content .= "\n\n" . $routes;
            
            if (!file_put_contents($routesFile, $content)) {
                $output->writeln("<error>Could not update the routes file: $routesFile</error>");
                return Command::FAILURE;
            }
            
            $relativePath = str_replace(App::$root, '', $routesFile);
            $output->writeln("<info>Routes added to => $relativePath</info>");
        }
        
        return Command::SUCCESS;
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
