<?php

namespace LightWeight\Storage;

use LightWeight\Storage\Drivers\Contracts\FileStorageDriverContract;
use LightWeight\Exceptions\ConfigurationException;

/**
 * Manages storage drivers and provides a way to switch between them.
 */
class StorageManager
{
    /**
     * The active drivers instances.
     *
     * @var array<string, FileStorageDriverContract>
     */
    protected static array $drivers = [];

    /**
     * Get a storage driver instance.
     *
     * @param string|null $driver
     * @return FileStorageDriverContract
     * @throws ConfigurationException
     */
    public static function driver(?string $driver = null): FileStorageDriverContract
    {
        $driver = $driver ?? config('storage.default', 'local');

        // Return cached driver instance if we have it
        if (isset(static::$drivers[$driver])) {
            return static::$drivers[$driver];
        }

        // We need to resolve the driver via the container
        // This will use the FileStorageDriverServiceProvider to create the driver
        if ($driver === config('storage.default', 'local')) {
            static::$drivers[$driver] = app(FileStorageDriverContract::class);
            return static::$drivers[$driver];
        }

        // Check if we have a custom driver configuration
        $driverConfig = config("storage.drivers.{$driver}");
        if (!empty($driverConfig) && isset($driverConfig['driver']) && $driverConfig['driver'] === $driver) {
            // Here we would normally instantiate the driver based on the configuration
            // For simplicity, we'll use the app container but in a real implementation
            // this would use a factory to create the specific driver instance
            try {
                static::$drivers[$driver] = app(FileStorageDriverContract::class);
                return static::$drivers[$driver];
            } catch (\Throwable $e) {
                throw new ConfigurationException("Failed to instantiate storage driver '{$driver}': " . $e->getMessage());
            }
        }

        // For a non-default driver, we'll need to create it manually
        // This would require additional implementation for dynamic driver creation
        throw new ConfigurationException("Storage driver '{$driver}' is not configured properly");
    }

    /**
     * Register a custom storage driver.
     *
     * @param string $name The name of the driver
     * @param FileStorageDriverContract $driver The driver implementation
     * @return void
     */
    public static function registerDriver(string $name, FileStorageDriverContract $driver): void
    {
        static::$drivers[$name] = $driver;
    }

    /**
     * Reset the managed driver instances.
     * This is primarily useful for testing.
     *
     * @return void
     */
    public static function reset(): void
    {
        static::$drivers = [];
    }
}
