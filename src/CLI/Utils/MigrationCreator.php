<?php

namespace LightWeight\CLI\Utils;

use LightWeight\App;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

class MigrationCreator
{
    /**
     * Creates a migration for the model's table
     *
     * @param OutputInterface $output
     * @param string $tableName
     * @return int
     */
    public function createMigration(OutputInterface $output, string $tableName): int
    {
        try {
            // Verify that the migrations directory exists
            $migrationsDir = App::$root . "/database/migrations";
            if (!is_dir(App::$root . "/database")) {
                if (!mkdir(App::$root . "/database", 0755, true)) {
                    $output->writeln("<error>Could not create database directory: " . App::$root . "/database</error>");
                    return Command::FAILURE;
                }
            }
            
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
}
