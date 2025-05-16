<?php

namespace Tests\Exceptions;

use PHPUnit\Framework\TestCase;
use LightWeight\Exceptions\ExceptionNotifier;
use LightWeight\Exceptions\CriticalException;
use LightWeight\Exceptions\ExceptionLogger;

class ExceptionNotifierTest extends TestCase
{
    /**
     * @var bool Flag to track if log method was called
     */
    private static bool $logWasCalled = false;
    
    public function setUp(): void
    {
        parent::setUp();
        
        // Initialize App::$root
        if (!isset(\LightWeight\App::$root)) {
            \LightWeight\App::$root = __DIR__ . '/../..';
        }
        
        // Reset log tracking
        self::$logWasCalled = false;
        
        // Mock environment function
        if (!function_exists('env')) {
            function env($key, $default = null) {
                return $default;
            }
        }
        
        // Mock config function
        if (!function_exists('config')) {
            function config($key, $default = null) {
                if ($key === 'exceptions.notifications.email.to') {
                    return 'test@example.com';
                }
                
                if ($key === 'exceptions.log.daily') {
                    return false;
                }
                
                return $default;
            }
        }
    }
    
    public function testNotifierInitialization(): void
    {
        ExceptionNotifier::init([
            'channels' => ['log', 'email']
        ]);
        
        // Using reflection to test if channels were set correctly
        $reflection = new \ReflectionClass(ExceptionNotifier::class);
        $property = $reflection->getProperty('channels');
        $property->setAccessible(true);
        
        $this->assertEquals(['log', 'email'], $property->getValue());
    }
    
    public function testFormatExceptionForNotification(): void
    {
        $exception = new \Exception('Test exception');
        
        // Using reflection to access protected method
        $reflection = new \ReflectionClass(ExceptionNotifier::class);
        $method = $reflection->getMethod('formatExceptionForNotification');
        $method->setAccessible(true);
        
        $result = $method->invoke(null, $exception);
        
        // Check that the formatted exception contains expected keys
        $this->assertArrayHasKey('subject', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('exception', $result);
        $this->assertArrayHasKey('file', $result);
        $this->assertArrayHasKey('line', $result);
        $this->assertArrayHasKey('trace', $result);
        
        // Check that the exception details are correct
        $this->assertEquals('Test exception', $result['message']);
        $this->assertEquals('Exception', $result['exception']);
    }
    
    public function testNotifyWithCriticalException(): void
    {
        // Create a critical exception
        $exception = new CriticalException(
            'Critical system failure',
            'database',
            'critical',
            ['log', 'email']
        );
        
        // Since we can't easily mock static methods in PHP without additional libraries,
        // we will test that the notify method doesn't throw unhandled exceptions
        
        try {
            // Call the notify method with only log channel
            ExceptionNotifier::notify($exception, ['log']);
            
            // If we reach here, the method executed without throwing exceptions
            $this->assertTrue(true);
        } catch (\Throwable $e) {
            $this->fail('Exception thrown during notification: ' . $e->getMessage());
        }
    }
}
