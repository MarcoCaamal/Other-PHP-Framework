<?php

namespace LightWeight\CLI\Utils;

use LightWeight\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

class ViewCreator
{
    /**
     * Creates views for the web controller
     *
     * @param OutputInterface $output
     * @param string $viewName (can include namespaces like "Products\Test")
     * @param string|null $namespace Optional explicit namespace
     * @return int
     */
    public function createViews(OutputInterface $output, string $viewName, ?string $namespace = null): int
    {
        // Parse view name and namespace if it contains backslashes
        $parts = explode('\\', $viewName);
        $baseName = array_pop($parts);
        
        // If an explicit namespace is provided and our view name doesn't already have one
        if (!empty($namespace) && empty($parts)) {
            $parts = explode('\\', $namespace);
        }
        
        // Ensure resources directory exists
        $resourcesDir = Application::$root . "/resources";
        if (!is_dir($resourcesDir)) {
            if (!mkdir($resourcesDir, 0755, true)) {
                $output->writeln("<error>Could not create resources directory: $resourcesDir</error>");
                return Command::FAILURE;
            }
        }
        
        // Ensure views base directory exists
        $viewsBaseDir = "$resourcesDir/views";
        if (!is_dir($viewsBaseDir)) {
            if (!mkdir($viewsBaseDir, 0755, true)) {
                $output->writeln("<error>Could not create views directory: $viewsBaseDir</error>");
                return Command::FAILURE;
            }
        }
        
        // Create path including any namespace parts (for subdirectories)
        $namespaceDir = empty($parts) ? '' : '/'. strtolower(implode('/', $parts));
        $viewsDir = "$viewsBaseDir$namespaceDir/" . strtolower($baseName);
        
        $output->writeln("<comment>Creating views in directory: $viewsDir</comment>");
        
        if (!is_dir($viewsDir)) {
            if (!mkdir($viewsDir, 0755, true)) {
                $output->writeln("<error>Could not create views directory: $viewsDir</error>");
                return Command::FAILURE;
            }
        }
        
        // Define the views to create
        $views = [
            'index' => 'view.index.template',
            'create' => 'view.create.template',
            'show' => 'view.show.template',
            'edit' => 'view.edit.template'
        ];
        
        // Create the views
        foreach ($views as $viewFile => $templateFile) {
            $viewPath = "$viewsDir/$viewFile.php";
            
            // Check if the view already exists
            if (file_exists($viewPath)) {
                $output->writeln("<comment>The view already exists: $viewPath</comment>");
                continue;
            }
            // Get view template
            $templatePath = dirname(__DIR__, 3) . "/templates/$templateFile";
            
            if (!file_exists($templatePath)) {
                $output->writeln("<comment>View template not found: $templateFile</comment>");
                continue;
            }
            
            $template = file_get_contents($templatePath);
            
            // Replace values in the template
            $routePath = $this->getRoutePath($viewName);
            $template = str_replace("ControllerNameRoute", $routePath, $template);
            $template = str_replace("{{ title }}", $baseName, $template);
            $template = str_replace("<?php echo \$title; ?>", "<?php echo '$baseName'; ?>", $template);
            $template = str_replace("<?php echo \$id; ?>", "<?php echo \$id; ?>", $template);
            
            // Save the view
            if (!file_put_contents($viewPath, $template)) {
                $output->writeln("<error>Could not create the view: $viewPath</error>");
                continue;
            }
            
            $relativePath = str_replace(Application::$root, '', $viewPath);
            $output->writeln("<info>View created => $relativePath</info>");
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
