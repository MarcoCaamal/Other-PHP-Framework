<?php

namespace Tests\Exceptions;

use LightWeight\Exceptions\ExceptionLogger;
use PHPUnit\Framework\TestCase;
use LightWeight\App;
use LightWeight\Config\Config;

class ExceptionLoggerTest extends TestCase
{
    protected $logFile;
    protected $originalRoot;
    protected $originalConfig;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Back up original configuration
        $this->originalConfig = Config::$config;
        
        // Set up a temporary directory for logs
        $this->originalRoot = App::$root ?? null;
        $tempDir = sys_get_temp_dir() . '/lightweight_test_' . uniqid();
        mkdir($tempDir . '/storage/logs', 0777, true);
        App::$root = $tempDir;
        
        // Initialize config for exceptions
        Config::$config['exceptions'] = [
            'log' => [
                'daily' => false,
                'max_files' => 30
            ]
        ];
        
        // Path to the log file
        $this->logFile = $tempDir . '/storage/logs/exceptions.log';
    }

    protected function tearDown(): void
    {
        // Restore original config
        Config::$config = $this->originalConfig;
        
        // Restore the original root
        if ($this->originalRoot !== null) {
            App::$root = $this->originalRoot;
        } else {
            // Cannot unset static properties in PHP 8.2+, set to empty string instead
            App::$root = '';
        }
        
        // Clean up log file if it exists
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }
        
        // Clean up directories
        $logsDir = dirname($this->logFile);
        $storageDir = dirname($logsDir);
        $tempDir = dirname($storageDir);
        
        if (is_dir($logsDir)) {
            // Clean up any test log files that might be left
            $logFiles = glob($logsDir . '/exceptions*.log');
            foreach ($logFiles as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
            rmdir($logsDir);
        }
        
        if (is_dir($storageDir)) rmdir($storageDir);
        if (is_dir($tempDir)) rmdir($tempDir);
        
        parent::tearDown();
    }

    public function testLogException(): void
    {
        // Create a test exception
        $exception = new \Exception('Test exception message');
        
        // Log the exception
        ExceptionLogger::log($exception, ExceptionLogger::ERROR);
        
        // Check if the log file exists and contains the exception message
        $this->assertFileExists($this->logFile);
        $logContent = file_get_contents($this->logFile);
        
        $this->assertStringContainsString('ERROR', $logContent);
        $this->assertStringContainsString('Test exception message', $logContent);
        $this->assertStringContainsString('ExceptionLoggerTest.php', $logContent);
    }

    public function testGetLogPath(): void
    {
        // Set daily logging to true
        Config::$config['exceptions']['log']['daily'] = true;
        
        // Use reflection to access protected method
        $reflectionClass = new \ReflectionClass(ExceptionLogger::class);
        $method = $reflectionClass->getMethod('getLogPath');
        $method->setAccessible(true);
        
        // Get log path
        $logPath = $method->invoke(null);
        
        // Check if log path has date format
        $this->assertStringContainsString(date('Y-m-d'), $logPath);
        
        // Test without daily rotation
        Config::$config['exceptions']['log']['daily'] = false;
        $logPath = $method->invoke(null);
        
        // Check if log path doesn't have date format
        $this->assertStringNotContainsString(date('Y-m-d'), $logPath);
    }

    public function testRotateLogs(): void
    {
        // Create multiple log files
        $logsDir = dirname($this->logFile);
        $files = [];
        
        for ($i = 1; $i <= 5; $i++) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $filename = $logsDir . '/exceptions-' . $date . '.log';
            file_put_contents($filename, "Test log content $i");
            $files[] = $filename;
            
            // Add a small delay to ensure different modification times
            usleep(10000);
        }
        
        // Set max files to 3
        Config::$config['exceptions']['log']['max_files'] = 3;
        
        // Rotate logs
        ExceptionLogger::rotateLogs();
        
        // Check that only the 3 newest files remain
        $this->assertFileExists($files[0]);
        $this->assertFileExists($files[1]);
        $this->assertFileExists($files[2]);
        $this->assertFileDoesNotExist($files[3]);
        $this->assertFileDoesNotExist($files[4]);
        
        // Clean up remaining test files
        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
}
