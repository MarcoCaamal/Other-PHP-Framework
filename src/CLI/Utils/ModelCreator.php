<?php

namespace LightWeight\CLI\Utils;

use LightWeight\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

class ModelCreator
{
    /**
     * Creates an associated model
     *
     * @param OutputInterface $output
     * @param string $modelName (can include subdirectories like "Admin\User")
     * @return int
     */
    public function createModel(OutputInterface $output, string $modelName): int
    {
        // Parse model name and namespace
        $parts = explode('\\', $modelName);
        $className = array_pop($parts);
        $namespace = implode('\\', $parts);

        $modelsDir = Application::$root . "/app/Models";

        // Create models directory if it doesn't exist
        if (!is_dir($modelsDir)) {
            if (!mkdir($modelsDir, 0755, true)) {
                $output->writeln("<error>Could not create models directory: $modelsDir</error>");
                return Command::FAILURE;
            }
        }
        // Add the namespace path if it exists
        $modelPath = $modelsDir;
        if (!empty($namespace)) {
            $modelPath .= '/' . str_replace('\\', '/', $namespace);

            // Create subdirectories if they don't exist
            if (!is_dir($modelPath)) {
                if (!mkdir($modelPath, 0755, true)) {
                    $output->writeln("<error>Could not create models subdirectory: $modelPath</error>");
                    return Command::FAILURE;
                }
            }
        }

        // Full path to the model file
        $modelFilePath = "$modelPath/$className.php";

        // Check if the model already exists
        if (file_exists($modelFilePath)) {
            $output->writeln("<comment>The model already exists: $modelFilePath</comment>");
            return Command::SUCCESS;
        }
        // Get model template
        $templatePath = dirname(__DIR__, 3) . "/templates/model.template";

        if (!file_exists($templatePath)) {
            $output->writeln("<error>Model template not found</error>");
            return Command::FAILURE;
        }

        $template = file_get_contents($templatePath);

        // Replace the model name
        $template = str_replace("ModelName", $className, $template);

        // Replace namespace if needed
        if (!empty($namespace)) {
            $template = str_replace("namespace App\\Models;", "namespace App\\Models\\$namespace;", $template);
        }

        // Replace the table name
        $tableName = $this->getTableName($className);
        $template = str_replace("table_name", $tableName, $template);

        // Save the model
        if (!file_put_contents($modelFilePath, $template)) {
            $output->writeln("<error>Could not create the model: $modelFilePath</error>");
            return Command::FAILURE;
        }

        $relativePath = str_replace(Application::$root, '', $modelFilePath);
        $output->writeln("<info>Model created => $relativePath</info>");

        return Command::SUCCESS;
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
}
