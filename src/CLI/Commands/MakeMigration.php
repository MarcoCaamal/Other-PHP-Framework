<?php

namespace LightWeight\CLI\Commands;

use LightWeight\Database\Migrations\Migrator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeMigration extends Command
{
    protected static $defaultName = "make:migration";
    protected static $defaultDescription = "Create new migration file";
    
    protected function configure()
    {
        $this
            ->addArgument("name", InputArgument::REQUIRED, "Migration name (e.g. create_users_table)")
            ->addOption(
                "fields", 
                "f", 
                InputOption::VALUE_OPTIONAL, 
                "Define fields for the migration (format: name:type,name2:type2,...)"
            )
            ->addOption(
                "table", 
                "t", 
                InputOption::VALUE_OPTIONAL, 
                "Table name (will be extracted from migration name if not provided)"
            )
            ->addOption(
                "type", 
                null, 
                InputOption::VALUE_OPTIONAL, 
                "Migration type (create, alter, update, custom)", 
                "auto"
            )
            ->setHelp(
                <<<EOT
                Create a new migration file.
                
                Usage examples:
                  <info>php light make:migration create_users_table</info>
                  <info>php light make:migration create_products_table --fields="name:string,price:decimal,description:text"</info>
                  <info>php light make:migration add_status_to_orders_table --fields="status:enum:pending,processing,completed"</info>
                  
                Available column types: id, string, integer, decimal, boolean, text, date, datetime, timestamp, enum
                EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Creating Migration');
        
        // Get inputs
        $name = $input->getArgument('name');
        $fields = $input->getOption('fields');
        $table = $input->getOption('table');
        $type = $input->getOption('type');
        
        // Parse fields if provided
        $parsedFields = [];
        if ($fields) {
            $parsedFields = $this->parseFields($fields);
        }
        
        // Create the migration
        $fileName = app(Migrator::class)->make($name, [
            'fields' => $parsedFields,
            'table' => $table,
            'type' => $type
        ]);
        
        $io->success("Migration created: $fileName");
        return Command::SUCCESS;
    }
    
    /**
     * Parse field definitions from command line
     * 
     * @param string $fields Field definitions in format name:type,name2:type2,...
     * @return array Structured field definitions
     */
    protected function parseFields(string $fields): array
    {
        $parsedFields = [];
        $fieldDefinitions = explode(',', $fields);
        
        foreach ($fieldDefinitions as $fieldDefinition) {
            $parts = explode(':', $fieldDefinition);
            
            // Basic validation
            if (count($parts) < 2) {
                continue;
            }
            
            $name = trim($parts[0]);
            $type = trim($parts[1]);
            
            $field = [
                'name' => $name,
                'type' => $type
            ];
            
            // Check for additional parameters
            if (count($parts) > 2) {
                // For enum values or other parameters
                $field['parameters'] = array_slice($parts, 2);
            }
            
            $parsedFields[] = $field;
        }
        
        return $parsedFields;
    }
}
