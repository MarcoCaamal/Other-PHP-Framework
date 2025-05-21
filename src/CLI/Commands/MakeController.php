<?php

namespace LightWeight\CLI\Commands;

use LightWeight\Application;
use LightWeight\CLI\Utils\ControllerCreator;
use LightWeight\CLI\Utils\ModelCreator;
use LightWeight\CLI\Utils\ViewCreator;
use LightWeight\CLI\Utils\RouteCreator;
use LightWeight\CLI\Utils\MigrationCreator;
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
    
    // Utility instances
    protected ControllerCreator $controllerCreator;
    protected ModelCreator $modelCreator;
    protected ViewCreator $viewCreator;
    protected RouteCreator $routeCreator;
    protected MigrationCreator $migrationCreator;
    
    public function __construct()
    {
        parent::__construct();
        
        // Initialize utilities
        $this->controllerCreator = new ControllerCreator();
        $this->modelCreator = new ModelCreator();
        $this->viewCreator = new ViewCreator();
        $this->routeCreator = new RouteCreator();
        $this->migrationCreator = new MigrationCreator();
    }
    
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
        
        // Create the controller using the controller creator utility
        list($status, $controllerName, $namespace, $baseControllerName) = 
            $this->controllerCreator->createController($output, $name, $type);
        
        if ($status === Command::FAILURE) {
            return Command::FAILURE;
        }
          // Create associated resources
        $results = [];
        
        // Create model if specified
        if ($createModel) {
            // If model name is explicitly provided, use it
            if ($modelName !== null) {
                $fullModelName = $modelName;
            } else {
                // Otherwise use controller name and namespace
                $fullModelName = empty($namespace) ? $baseControllerName : $namespace . '\\' . $baseControllerName;
            }
            
            $modelResult = $this->modelCreator->createModel($output, $fullModelName);
            $results[] = $modelResult;
        }
        
        // Create migration if specified
        if ($createMigration) {
            $tableName = tableName($baseControllerName);
            $migrationResult = $this->migrationCreator->createMigration($output, $tableName);
            $results[] = $migrationResult;
        }
          // Create views if specified and it's a web controller
        if ($createViews && $type === 'web') {
            $viewName = empty($namespace) ? $baseControllerName : $baseControllerName;
            $viewsResult = $this->viewCreator->createViews($output, $viewName, $namespace);
            $results[] = $viewsResult;
        } elseif ($createViews && $type !== 'web') {
            $output->writeln("<comment>Views are only created for 'web' type controllers</comment>");
        }
        
        // Create or update routes if specified and not a basic controller
        if ($createRoutes && $type !== 'basic') {
            $routesResult = $this->routeCreator->createRoutes(
                $output, 
                $baseControllerName, 
                $namespace, 
                $createRoutes, 
                $type
            );
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
}
