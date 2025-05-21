<?php

namespace LightWeight\Tests\Helpers;

use LightWeight\Application;
use LightWeight\Container\Container;
use LightWeight\Log\Contracts\LoggerContract;
use LightWeight\Log\Logger;
use PHPUnit\Framework\TestCase;

class LogHelperTest extends TestCase
{
    protected $loggerMock;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a mock logger
        $this->loggerMock = $this->createMock(LoggerContract::class);
        
        // Register the mock in the container
        $container = Container::getInstance();
        $container->set(LoggerContract::class, $this->loggerMock);
    }
    
    public function testLoggerHelperReturnsLoggerInstance()
    {
        $result = logger();
        
        $this->assertSame($this->loggerMock, $result);
    }
    
    public function testLogMessageHelperCallsLoggerWithCorrectParameters()
    {
        $message = 'Test message';
        $context = ['key' => 'value'];
        $level = 'error';
        
        // Set expectations on the mock
        $this->loggerMock->expects($this->once())
            ->method('log')
            ->with($level, $message, $context);
        
        // Call the log helper
        logMessage($message, $context, $level);
    }
    
    public function testLogMessageHelperUsesDefaultLevel()
    {
        $message = 'Test message';
        $context = ['key' => 'value'];
        
        // Set expectations on the mock
        $this->loggerMock->expects($this->once())
            ->method('log')
            ->with('info', $message, $context);
        
        // Call the log helper without specifying level
        logMessage($message, $context);
    }
}
