<?php

namespace LightWeight\CLI\Commands;

use LightWeight\App;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MakeController extends Command
{
    protected static $defaultName = "make:controller";
    protected static $defaultDescription = "Create a new controller";
    
    // Define available controller types
    protected array $controllerTypes = [
        'basic' => 'controller.template',
        'api' => 'controller.api.template',
        'resource' => 'controller.resource.template',
        'web' => 'controller.web.template',
    ];
    
    protected function configure()
    {
        $this
            ->addArgument("name", InputArgument::REQUIRED, "Controller name (can include subdirectories like Services\\Service)")
            ->addOption("type", "t", InputOption::VALUE_OPTIONAL, "Controller type (basic, api, resource, web)", "basic")
            ->addOption("model", "m", InputOption::VALUE_OPTIONAL, "Create associated model", null)
            ->addOption("migration", null, InputOption::VALUE_NONE, "Create associated migration")
            ->addOption("views", "w", InputOption::VALUE_NONE, "Create associated views (only for web type)")
            ->addOption("routes", "r", InputOption::VALUE_OPTIONAL, "Add routes to a file (provide filename or leave empty for default)", null)
            ->addOption("all", "a", InputOption::VALUE_NONE, "Create all associated resources");
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument("name");
        $type = $input->getOption("type");
        $createModel = $input->getOption("model") !== null;
        $modelName = $input->getOption("model") ?: null;
        $createMigration = $input->getOption("migration");
        $createViews = $input->getOption("views");
        $createRoutes = $input->getOption("routes");
        $createAll = $input->getOption("all");
        
        // If "all" option was specified, activate all options based on controller type
        if ($createAll) {
            $createModel = true;
            $createMigration = true;
            
            // Only create views and routes for non-basic controllers
            if ($type !== 'basic') {
                $createViews = ($type === 'web'); // Only create views for web controllers
                $createRoutes = $type; // Use controller type as the route file name
            }
        }
        
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
                return Command::FAILURE;
            }
        }
        
        if (!is_dir($dirPath)) {
            if (!mkdir($dirPath, 0755, true)) {
                $output->writeln("<error>Could not create directory: $dirPath</error>");
                return Command::FAILURE;
            }
        }
        
        // If views are needed, verify that the resources/views directory exists
        if ($createViews || $createAll) {
            $viewsBaseDir = App::$root . "/resources/views";
            if (!is_dir(App::$root . "/resources")) {
                if (!mkdir(App::$root . "/resources", 0755, true)) {
                    $output->writeln("<error>Could not create resources directory: " . App::$root . "/resources</error>");
                    return Command::FAILURE;
                }
            }
            if (!is_dir($viewsBaseDir)) {
                if (!mkdir($viewsBaseDir, 0755, true)) {
                    $output->writeln("<error>Could not create views directory: $viewsBaseDir</error>");
                    return Command::FAILURE;
                }
            }
        }
        
        // If routes are going to be created, verify that the routes directory exists
        if ($createRoutes || $createAll) {
            $routesDir = App::$root . "/routes";
            if (!is_dir($routesDir)) {
                if (!mkdir($routesDir, 0755, true)) {
                    $output->writeln("<error>Could not create routes directory: $routesDir</error>");
                    return Command::FAILURE;
                }
            }
        }
        
        if (!empty($namespace)) {
            $dirPath .= '/' . str_replace('\\', '/', $namespace);
            
            // Create subdirectories if they don't exist
            if (!is_dir($dirPath)) {
                if (!mkdir($dirPath, 0755, true)) {
                    $output->writeln("<error>Could not create directory: $dirPath</error>");
                    return Command::FAILURE;
                }
            }
        }
        
        // Validate the controller type
        if (!array_key_exists($type, $this->controllerTypes)) {
            $output->writeln("<error>Invalid controller type: $type</error>");
            $output->writeln("Available types: " . implode(", ", array_keys($this->controllerTypes)));
            return Command::FAILURE;
        }
        
        // Get the template according to the controller type
        $templateFile = $this->controllerTypes[$type];
        $templatePath = dirname(dirname(dirname(__DIR__))) . "/templates/$templateFile";
        
        if (!file_exists($templatePath)) {
            $output->writeln("<error>Template not found: $templatePath</error>");
            return Command::FAILURE;
        }
        
        $template = file_get_contents($templatePath);
        
        // Extract the base name for other resources
        $baseControllerName = str_replace('Controller', '', $controllerName);
        
        // Replace the controller name in the template
        $template = str_replace("ControllerName", $controllerName, $template);
        
        // If it's a web controller, also replace the view name
        if ($type === 'web') {
            $template = str_replace("ControllerNameView", $baseControllerName, $template);
            $template = str_replace("ControllerNameRoute", routePath($baseControllerName), $template);
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
            return Command::FAILURE;
        }
        
        // Save the file
        if (!file_put_contents($filePath, $template)) {
            $output->writeln("<error>Could not create the controller: $filePath</error>");
            return Command::FAILURE;
        }
        
        $relativePath = str_replace(App::$root, '', $filePath);
        $output->writeln("<info>Controller created => $relativePath</info>");
        
        // Create associated resources
        $results = [];
        
        // Create model if specified
        if ($createModel) {
            $modelResult = $this->createModel($output, $modelName ?: $baseControllerName);
            $results[] = $modelResult;
        }
        
        // Create migration if specified
        if ($createMigration) {
            $tableName = tableName($baseControllerName);
            $migrationResult = $this->createMigration($output, $tableName);
            $results[] = $migrationResult;
        }
        
        // Create views if specified and it's a web controller
        if ($createViews && $type === 'web') {
            $viewsResult = $this->createViews($output, $baseControllerName);
            $results[] = $viewsResult;
        } elseif ($createViews && $type !== 'web') {
            $output->writeln("<comment>Views are only created for 'web' type controllers</comment>");
        }
        
        // Create or update routes if specified and not a basic controller
        if ($createRoutes && $type !== 'basic') {
            $routesResult = $this->createRoutes($output, $baseControllerName, $namespace, $createRoutes, $type);
            $results[] = $routesResult;
        } else if ($createRoutes && $type === 'basic') {
            $output->writeln("<comment>Routes are not created for 'basic' type controllers as they have no methods</comment>");
        }
        
        // Check if any operation failed
        if (in_array(Command::FAILURE, $results)) {
            return Command::FAILURE;
        }
        
        return Command::SUCCESS;
    }
    
    /**
     * Creates an associated model for the controller.
     *
     * @param OutputInterface $output
     * @param string $modelName
     * @return int
     */
    protected function createModel(OutputInterface $output, string $modelName): int
    {
        $modelsDir = App::$root . "/app/Models";
        
        // Create models directory if it doesn't exist
        if (!is_dir($modelsDir)) {
            if (!mkdir($modelsDir, 0755, true)) {
                $output->writeln("<error>Could not create models directory: $modelsDir</error>");
                return Command::FAILURE;
            }
        }
        
        // Model path
        $modelPath = "$modelsDir/$modelName.php";
        
        // Check if the model already exists
        if (file_exists($modelPath)) {
            $output->writeln("<comment>The model already exists: $modelPath</comment>");
            return Command::SUCCESS;
        }
        
        // Get model template
        $templatePath = dirname(dirname(dirname(__DIR__))) . "/templates/model.template";
        
        if (!file_exists($templatePath)) {
            $output->writeln("<error>Model template not found</error>");
            return Command::FAILURE;
        }
        
        $template = file_get_contents($templatePath);
        
        // Replace the model name
        $template = str_replace("ModelName", $modelName, $template);
        
        // Replace the table name
        $tableName = tableName($modelName);
        $template = str_replace("table_name", $tableName, $template);
        
        // Save the model
        if (!file_put_contents($modelPath, $template)) {
            $output->writeln("<error>Could not create the model: $modelPath</error>");
            return Command::FAILURE;
        }
        
        $relativePath = str_replace(App::$root, '', $modelPath);
        $output->writeln("<info>Model created => $relativePath</info>");
        
        return Command::SUCCESS;
    }
    
    /**
     * Creates a migration for the model's table.
     *
     * @param OutputInterface $output
     * @param string $tableName
     * @return int
     */
    protected function createMigration(OutputInterface $output, string $tableName): int
    {
        try {
            // Verify that the migrations directory exists
            $migrationsDir = App::$root . "/database/migrations";
            if (!is_dir($migrationsDir)) {
                if (!mkdir($migrationsDir, 0755, true)) {
                    $output->writeln("<error>Could not create migrations directory: $migrationsDir</error>");
                    return Command::FAILURE;
                }
            }
            
            // Use the Migrator class to create the migration
            $migrationName = "create_{$tableName}_table";
            
            $migrator = app(\LightWeight\Database\Migrations\Migrator::class);
            $fileName = $migrator->make($migrationName);
            
            $output->writeln("<info>Migration created => $fileName</info>");
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $output->writeln("<error>Error creating migration: " . $e->getMessage() . "</error>");
            return Command::FAILURE;
        }
    }
    
    /**
     * Creates views for the web controller.
     *
     * @param OutputInterface $output
     * @param string $viewName
     * @return int
     */
    protected function createViews(OutputInterface $output, string $viewName): int
    {
        $viewsDir = App::$root . "/resources/views/$viewName";
        
        // Create views directory if it doesn't exist
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
            $templatePath = dirname(dirname(dirname(__DIR__))) . "/templates/$templateFile";
            
            if (!file_exists($templatePath)) {
                $output->writeln("<comment>View template not found: $templateFile</comment>");
                continue;
            }
            
            $template = file_get_contents($templatePath);
            
            // Replace values in the template
            $routePath = routePath($viewName);
            $template = str_replace("ControllerNameRoute", $routePath, $template);
            $template = str_replace("{{ title }}", $viewName, $template);
            $template = str_replace("<?php echo \$title; ?>", "<?php echo '$viewName'; ?>", $template);
            $template = str_replace("<?php echo \$id; ?>", "<?php echo \$id; ?>", $template);
            
            // Save the view
            if (!file_put_contents($viewPath, $template)) {
                $output->writeln("<error>Could not create the view: $viewPath</error>");
                continue;
            }
            
            $relativePath = str_replace(App::$root, '', $viewPath);
            $output->writeln("<info>View created => $relativePath</info>");
        }
        
        return Command::SUCCESS;
    }
    
    /**
     * Creates or updates route files.
     *
     * @param OutputInterface $output
     * @param string $controllerName
     * @param string $namespace
     * @param string $routeFileName The name of the route file to create or update
     * @param string $controllerType The type of controller (api, web, resource)
     * @return int
     */
    protected function createRoutes(OutputInterface $output, string $controllerName, string $namespace, string $routeFileName, string $controllerType): int
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
        $templatePath = dirname(dirname(dirname(__DIR__))) . "/templates/routes.template";
        
        if (!file_exists($templatePath)) {
            $output->writeln("<error>Routes template not found</error>");
            return Command::FAILURE;
        }
        
        $template = file_get_contents($templatePath);
        
        // Replace values in the template
        $routePath = routePath($controllerName);
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
    
    protected function getTableName(string $input): string
    {
        // Use the helper function from helpers/string.php
        return tableName($input);
    }
    
    protected function getRoutePath(string $controllerName): string
    {
        // Use the helper function from helpers/string.php
        return routePath($controllerName);
    }
}
