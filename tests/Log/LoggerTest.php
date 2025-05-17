<?php

namespace LightWeight\Tests\Log;

use LightWeight\App;
use LightWeight\Log\Contracts\LoggerContract;
use LightWeight\Log\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
    protected Logger $logger;
    protected string $logFile;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a temporary log file for testing
        $this->logFile = sys_get_temp_dir() . '/lightweight_test_' . uniqid() . '.log';
        
        // Create logger with a stream handler to the temporary file
        $this->logger = new Logger('test');
        $this->logger->pushHandler(new StreamHandler($this->logFile, Level::Debug));
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Delete the temporary log file
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }
    }
    
    public function testImplementsLoggerContract()
    {
        $this->assertInstanceOf(LoggerContract::class, $this->logger);
    }
    
    public function testLogsMessages()
    {
        $testMessage = 'Test log message ' . uniqid();
        
        // Log a test message
        $this->logger->info($testMessage);
        
        // Check if the log file contains the message
        $logContent = file_get_contents($this->logFile);
        $this->assertStringContainsString($testMessage, $logContent);
    }
    
    public function testLogsWithDifferentLevels()
    {
        // Log messages with different levels
        $this->logger->debug('Debug message');
        $this->logger->info('Info message');
        $this->logger->warning('Warning message');
        $this->logger->error('Error message');
        
        // Check if the log file contains all messages
        $logContent = file_get_contents($this->logFile);
        $this->assertStringContainsString('DEBUG', $logContent);
        $this->assertStringContainsString('INFO', $logContent);
        $this->assertStringContainsString('WARNING', $logContent);
        $this->assertStringContainsString('ERROR', $logContent);
    }
    
    public function testLogsContext()
    {
        $context = ['user_id' => 123, 'action' => 'login'];
        
        // Log a message with context
        $this->logger->info('User action', $context);
        
        // Check if the log file contains the context
        $logContent = file_get_contents($this->logFile);
        $this->assertStringContainsString('user_id', $logContent);
        $this->assertStringContainsString('123', $logContent);
        $this->assertStringContainsString('action', $logContent);
        $this->assertStringContainsString('login', $logContent);
    }
}
