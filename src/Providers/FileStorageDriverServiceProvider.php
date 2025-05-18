<?php

namespace LightWeight\Providers;

use LightWeight\Providers\Contracts\ServiceProviderContract;
use LightWeight\Storage\Drivers\Contracts\FileStorageDriverContract;
use LightWeight\Storage\Drivers\LocalFileStorage;
use LightWeight\Storage\Drivers\PublicFileStorage;
use LightWeight\Exceptions\ConfigurationException;

class FileStorageDriverServiceProvider implements ServiceProviderContract
{
    /**
     * @inheritDoc
     */
    public function registerServices(\DI\Container $serviceContainer)
    {
        $default = config("storage.default", "local");
        $driverConfig = config("storage.drivers.$default", null);
        
        if (!$driverConfig) {
            throw new ConfigurationException("Storage driver configuration for '$default' not found");
        }
        
        $driver = $driverConfig['driver'] ?? $default;
        
        // Register the appropriate driver based on configuration
        match ($driver) {
            "disk", "local" => $this->registerLocalDriver($serviceContainer, $driverConfig),
            "public" => $this->registerPublicDriver($serviceContainer, $driverConfig),
            "s3" => $this->registerS3Driver($serviceContainer, $driverConfig),
            "ftp" => $this->registerFtpDriver($serviceContainer, $driverConfig),
            default => throw new ConfigurationException("Unsupported storage driver: $driver")
        };
    }
    
    /**
     * Register local driver
     *
     * @param \DI\Container $serviceContainer
     * @param array $config
     * @return void
     */
    protected function registerLocalDriver(\DI\Container $serviceContainer, array $config): void
    {
        $serviceContainer->set(
            FileStorageDriverContract::class, 
            \DI\create(LocalFileStorage::class)->constructor(
                $config['path'] ?? config('storage.path', rootDirectory() . '/storage/app')
            )
        );
    }
    
    /**
     * Register public driver
     *
     * @param \DI\Container $serviceContainer
     * @param array $config
     * @return void
     */
    protected function registerPublicDriver(\DI\Container $serviceContainer, array $config): void
    {
        $serviceContainer->set(
            FileStorageDriverContract::class, 
            \DI\create(PublicFileStorage::class)->constructor(
                $config['path'] ?? config('storage.path', rootDirectory() . '/storage/public'),
                $config['storage_uri'] ?? config('storage.storage_uri', 'storage/public'),
                $config['url'] ?? config('storage.url', 'http://localhost')
            )
        );
    }
    
    /**
     * Register S3 driver
     *
     * @param \DI\Container $serviceContainer
     * @param array $config
     * @return void
     */
    protected function registerS3Driver(\DI\Container $serviceContainer, array $config): void
    {
        // This is a placeholder for future S3 driver implementation
        throw new ConfigurationException("S3 driver not yet implemented");
    }
    
    /**
     * Register FTP driver
     *
     * @param \DI\Container $serviceContainer
     * @param array $config
     * @return void
     */
    protected function registerFtpDriver(\DI\Container $serviceContainer, array $config): void
    {
        // This is a placeholder for future FTP driver implementation
        throw new ConfigurationException("FTP driver not yet implemented");
    }
}
