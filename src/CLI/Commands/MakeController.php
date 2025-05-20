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
      // Definir tipos de controladores disponibles
    protected array $controllerTypes = [
        'basic' => 'controller.template',
        'api' => 'controller.api.template',
        'resource' => 'controller.resource.template',
    ];
    
    protected function configure()
    {
        $this
            ->addArgument("name", InputArgument::REQUIRED, "Controller name (puede incluir subdirectorios como Services\\Service)")
            ->addOption("type", "t", InputOption::VALUE_OPTIONAL, "Tipo de controlador (basic, api, resource)", "basic");
    }
      protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument("name");
        $type = $input->getOption("type");
        
        // Convertir barras diagonales normales a barras invertidas para namespaces
        $name = str_replace('/', '\\', $name);
        
        // Separar el namespace y el nombre del controlador
        $parts = explode('\\', $name);
        $controllerName = array_pop($parts); // Último elemento es el nombre del controlador
        $namespace = implode('\\', $parts); // El resto es el namespace
        
        // Asegurarse que el nombre del controlador termine con "Controller"
        if (!str_ends_with($controllerName, 'Controller')) {
            $controllerName .= 'Controller';
        }
        
        // Construir la ruta del directorio
        $dirPath = App::$root . "/app/Controllers";
        if (!empty($namespace)) {
            $dirPath .= '/' . str_replace('\\', '/', $namespace);
        }
        
        // Crear directorios si no existen
        if (!is_dir($dirPath)) {
            if (!mkdir($dirPath, 0755, true)) {
                $output->writeln("<error>No se pudo crear el directorio: $dirPath</error>");
                return Command::FAILURE;
            }
        }
        
        // Obtener la plantilla según el tipo de controlador
        if (!array_key_exists($type, $this->controllerTypes)) {
            $output->writeln("<error>Tipo de controlador no válido: $type</error>");
            $output->writeln("Tipos disponibles: " . implode(", ", array_keys($this->controllerTypes)));
            return Command::FAILURE;
        }
        
        $templateFile = $this->controllerTypes[$type];
        $template = file_get_contents(dirname(dirname(dirname(__DIR__))) . "/templates/$templateFile");
        $template = str_replace("ControllerName", $controllerName, $template);
        
        // Si hay namespace, agregarlo a la plantilla
        if (!empty($namespace)) {
            $fullNamespace = "App\\Controllers\\$namespace";
            $template = str_replace("namespace App\\Controllers;", "namespace $fullNamespace;", $template);
        }
        
        // Ruta completa del archivo
        $filePath = "$dirPath/$controllerName.php";
        
        // Verificar si el archivo ya existe
        if (file_exists($filePath)) {
            $output->writeln("<error>El controlador ya existe: $filePath</error>");
            $output->writeln("Usa diferentes opciones o elimina el archivo existente.");
            return Command::FAILURE;
        }
        
        // Guardar el archivo
        if (file_put_contents($filePath, $template)) {
            $relativePath = str_replace(App::$root, '', $filePath);
            $output->writeln("<info>Controlador creado => $relativePath</info>");
            return Command::SUCCESS;
        } else {
            $output->writeln("<error>No se pudo crear el controlador: $filePath</error>");
            return Command::FAILURE;
        }
    }
}
