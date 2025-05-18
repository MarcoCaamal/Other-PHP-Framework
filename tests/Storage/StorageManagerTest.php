<?php

namespace LightWeight\Tests\Storage;

use LightWeight\Container\Container;
use LightWeight\Exceptions\ConfigurationException;
use LightWeight\Storage\Drivers\Contracts\FileStorageDriverContract;
use LightWeight\Storage\StorageManager;
use LightWeight\Config\Config;
use LightWeight\Tests\Storage\Mocks\MockLocalFileStorage;
use LightWeight\Tests\Storage\Mocks\MockPublicFileStorage;
use PHPUnit\Framework\TestCase;

class StorageManagerTest extends TestCase
{
    protected $originalConfig = [];
    protected $mockDrivers = [];
    
    protected function setUp(): void
    {
        parent::setUp();
        StorageManager::reset();
        
        // Backup original config values
        $this->originalConfig = Config::$config;
        Config::$config = [];
        
        // Get container instance
        $container = Container::getInstance();
    }
    
    protected function tearDown(): void
    {
        // Restore original config
        Config::$config = $this->originalConfig;
        StorageManager::reset();
        parent::tearDown();
    }
    
    public function testDriverReturnsDefaultDriverWhenNoDriverSpecified()
    {
        // Setup
        Config::$config['storage.default'] = 'local';
        Config::$config['storage.drivers.local'] = [
            'driver' => 'local',
            'path' => '/tmp/storage/app'
        ];
        
        // Register our mock driver in the container
        $mockDriver = new MockLocalFileStorage('/tmp/storage/app');
        Container::getInstance()->set(FileStorageDriverContract::class, $mockDriver);
        
        // Act
        $driver = StorageManager::driver();
        
        // Assert
        $this->assertSame($mockDriver, $driver);
    }
    
    public function testDriverThrowsExceptionForNonDefaultDriver()
    {
        // Setup
        Config::$config['storage.default'] = 'disk';
        
        // Expect exception
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("Storage driver 'non-existent' is not configured properly");
        
        // Act
        StorageManager::driver('non-existent');
    }
    
    public function testDriverReturnsCachedInstanceForRepeatedCalls()
    {
        // Setup
        Config::$config['storage.default'] = 'local';
        Config::$config['storage.drivers.local'] = [
            'driver' => 'local',
            'path' => '/tmp/storage/app'
        ];
        
        // Register our mock driver in the container
        $mockDriver = new MockLocalFileStorage('/tmp/storage/app');
        Container::getInstance()->set(FileStorageDriverContract::class, $mockDriver);
        
        // Act
        $driver1 = StorageManager::driver();
        
        // Create a different mock and set it in the container
        // This should not affect the cached instance
        $newMockDriver = new MockLocalFileStorage('/different/path');
        Container::getInstance()->set(FileStorageDriverContract::class, $newMockDriver);
        
        $driver2 = StorageManager::driver();
        
        // Assert
        $this->assertSame($driver1, $driver2);
        $this->assertNotSame($newMockDriver, $driver2);
    }
    
    public function testResetClearsDriverInstances()
    {
        // Setup
        Config::$config['storage.default'] = 'local';
        Config::$config['storage.drivers.local'] = [
            'driver' => 'local',
            'path' => '/tmp/storage/app'
        ];
        
        // Register our mock driver in the container
        $mockDriver1 = new MockLocalFileStorage('/tmp/storage/app');
        Container::getInstance()->set(FileStorageDriverContract::class, $mockDriver1);
        
        // Get a driver instance
        $driver1 = StorageManager::driver();
        
        // Reset drivers
        StorageManager::reset();
        
        // Create a new mock driver
        $mockDriver2 = new MockLocalFileStorage('/tmp/storage/app/new');
        Container::getInstance()->set(FileStorageDriverContract::class, $mockDriver2);
        
        // Get a new driver instance
        $driver2 = StorageManager::driver();
        
        // Assert
        $this->assertNotSame($driver1, $driver2);
    }
    
    public function testDriverLocalImplementation()
    {
        // Setup
        Config::$config['storage.default'] = 'local';
        Config::$config['storage.drivers.local'] = [
            'driver' => 'local',
            'path' => '/tmp/storage/app',
        ];
        
        // Create a mock for the LocalFileStorage
        $mockLocalDriver = new MockLocalFileStorage('/tmp/storage/app');
        Container::getInstance()->set(FileStorageDriverContract::class, $mockLocalDriver);
        
        // Act
        $driver = StorageManager::driver('local');
        
        // Assert
        $this->assertSame($mockLocalDriver, $driver);
        $this->assertInstanceOf(FileStorageDriverContract::class, $driver);
    }
    
    public function testDriverPublicImplementation()
    {
        // Setup - Important: setting 'public' as the default driver
        Config::$config['storage.default'] = 'public';
        Config::$config['storage.drivers.public'] = [
            'driver' => 'public',
            'path' => '/tmp/storage/public',
            'storage_uri' => 'uploads',
            'url' => 'http://example.com'
        ];
        
        // Create a mock for the PublicFileStorage
        $mockPublicDriver = new MockPublicFileStorage(
            '/tmp/storage/public',
            'uploads',
            'http://example.com'
        );
        Container::getInstance()->set(FileStorageDriverContract::class, $mockPublicDriver);
        
        // Act - Note: we're not passing a driver name here, since 'public' is now the default
        $driver = StorageManager::driver();
        
        // Assert
        $this->assertSame($mockPublicDriver, $driver);
        $this->assertInstanceOf(FileStorageDriverContract::class, $driver);
    }
    
    /**
     * Test that the storage manager can switch between different drivers
     */
    public function testSwitchingBetweenDrivers()
    {
        // Reset for this test
        StorageManager::reset();
        
        // Setup mocks for different drivers
        $mockLocalDriver = new MockLocalFileStorage('/tmp/storage/app');
        $mockPublicDriver = new MockPublicFileStorage(
            '/tmp/storage/public',
            'uploads',
            'http://example.com'
        );
        
        // Setup config for local driver
        Config::$config['storage.default'] = 'local';
        Config::$config['storage.drivers.local'] = ['driver' => 'local'];
        Container::getInstance()->set(FileStorageDriverContract::class, $mockLocalDriver);
        
        // First use local driver
        $localDriver = StorageManager::driver();
        $this->assertSame($mockLocalDriver, $localDriver);
        
        // Reset and switch to public driver
        StorageManager::reset();
        Config::$config['storage.default'] = 'public';
        Config::$config['storage.drivers.public'] = ['driver' => 'public'];
        Container::getInstance()->set(FileStorageDriverContract::class, $mockPublicDriver);
        
        // Use public driver
        $publicDriver = StorageManager::driver();
        $this->assertSame($mockPublicDriver, $publicDriver);
        
        // Verify they're different
        $this->assertNotSame($localDriver, $publicDriver);
    }
    
    /**
     * Test that a custom driver can be registered and used
     */
    public function testRegisterCustomDriver()
    {
        // Reset for a clean slate
        StorageManager::reset();
        
        // Create a custom driver instance
        $customDriver = new MockLocalFileStorage('/custom/path');
        
        // Register the custom driver
        StorageManager::registerDriver('custom', $customDriver);
        
        // Try to get the custom driver
        $retrievedDriver = StorageManager::driver('custom');
        
        // Assert
        $this->assertSame($customDriver, $retrievedDriver);
        $this->assertInstanceOf(FileStorageDriverContract::class, $retrievedDriver);
    }
    
    /**
     * Test that registering a driver with the same name overwrites the previous one
     */
    public function testRegisterDriverOverwritesPreviousDriver()
    {
        // Reset for a clean slate
        StorageManager::reset();
        
        // Create and register the first driver
        $firstDriver = new MockLocalFileStorage('/first/path');
        StorageManager::registerDriver('custom', $firstDriver);
        
        // Create and register another driver with the same name
        $secondDriver = new MockPublicFileStorage('/second/path', 'uploads', 'http://example.com');
        StorageManager::registerDriver('custom', $secondDriver);
        
        // Get the driver
        $retrievedDriver = StorageManager::driver('custom');
        
        // Assert that the second driver overwrote the first one
        $this->assertSame($secondDriver, $retrievedDriver);
        $this->assertNotSame($firstDriver, $retrievedDriver);
    }
    
    /**
     * Test that a registered driver persists across calls even when it's not default
     */
    public function testRegisteredDriverPersistsAcrossCalls()
    {
        // Reset for a clean slate
        StorageManager::reset();
        
        // Set local as default
        Config::$config['storage.default'] = 'local';
        
        // Create local driver for default
        $localDriver = new MockLocalFileStorage('/local/path');
        Container::getInstance()->set(FileStorageDriverContract::class, $localDriver);
        
        // Create and register a custom driver
        $customDriver = new MockPublicFileStorage('/custom/path', 'uploads', 'http://example.com');
        StorageManager::registerDriver('custom', $customDriver);
        
        // Get default driver first
        $defaultDriver = StorageManager::driver();
        
        // Now get custom driver
        $retrievedCustomDriver = StorageManager::driver('custom');
        
        // Then get default again
        $defaultDriverAgain = StorageManager::driver();
        
        // Assert
        $this->assertSame($localDriver, $defaultDriver);
        $this->assertSame($customDriver, $retrievedCustomDriver);
        $this->assertSame($defaultDriver, $defaultDriverAgain);
    }
}
