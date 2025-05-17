<?php

namespace LightWeight\CLI\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class InitApp extends Command
{
    protected static $defaultName = 'init';
    protected static $defaultDescription = 'Copy a project skeleton to create a new application';

    private string $templatesDir;

    public function __construct(string $templatesDir = null)
    {
        parent::__construct();
        $this->templatesDir = $templatesDir ?? dirname(dirname(dirname(__DIR__))) . '/templates/app';
    }
    
    protected function configure()
    {
        $this
            ->addArgument("name", InputArgument::OPTIONAL, "Directory name for the new application (defaults to current directory)", ".")
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force overwrite of existing files')
            ->setHelp('This command creates a new LightWeight application by copying the template files');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('LightWeight App Initializer');

        $appName = $input->getArgument('name');
        $force = $input->getOption('force');
        
        $targetDir = getcwd();
        if ($appName !== '.') {
            $targetDir .= "/$appName";
            
            // Check if directory already exists
            if (is_dir($targetDir) && !$force) {
                $io->error("Directory '{$appName}' already exists! Use --force to overwrite files.");
                return Command::FAILURE;
            }
            
            // Create target directory if it doesn't exist
            if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true)) {
                $io->error("Failed to create directory '{$appName}'");
                return Command::FAILURE;
            }
            
            $io->section("Creating new LightWeight application in {$appName}");
        } else {
            $io->section("Initializing LightWeight application in current directory");
        }
        
        // Check if templates directory exists
        if (!is_dir($this->templatesDir)) {
            $io->error("Templates directory not found: {$this->templatesDir}");
            return Command::FAILURE;
        }
        
        // Copy template files
        $this->copyTemplateFiles($this->templatesDir, $targetDir, $io, $force);
        
        // Success message
        $io->success("Application initialized successfully!");
        
        
        if ($appName !== '.') {
            $io->text("Run 'cd {$appName} && composer install' to install dependencies");
        } else {
            $io->text("Run 'composer install' to install dependencies");
        }
        
        return Command::SUCCESS;
    }
    
    private function copyTemplateFiles(string $sourceDir, string $targetDir, SymfonyStyle $io, bool $force)
    {
        if (!is_dir($sourceDir)) {
            $io->error("Source directory does not exist: {$sourceDir}");
            return;
        }
        
        $dir = opendir($sourceDir);
        
        while (($file = readdir($dir)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $sourcePath = $sourceDir . '/' . $file;
            $targetPath = $targetDir . '/' . $file;
            
            if (is_dir($sourcePath)) {
                if (!is_dir($targetPath)) {
                    if (mkdir($targetPath, 0755, true)) {
                        $io->text("Created directory: " . str_replace($targetDir . '/', '', $targetPath));
                    }
                }
                $this->copyTemplateFiles($sourcePath, $targetPath, $io, $force);
            } else {
                // Check if file already exists and if we should copy
                if (!file_exists($targetPath) || $force) {
                    if (copy($sourcePath, $targetPath)) {
                        $io->text("Created file: " . str_replace($targetDir . '/', '', $targetPath));
                    } else {
                        $io->warning("Failed to copy: " . str_replace($targetDir . '/', '', $targetPath));
                    }
                } else {
                    $io->text("Skipped existing file: " . str_replace($targetDir . '/', '', $targetPath));
                }
            }
        }
        
        closedir($dir);
    }
}
