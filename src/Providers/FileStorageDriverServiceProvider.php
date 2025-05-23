<?php

namespace LightWeight\Providers;

use LightWeight\Container\Container;
use LightWeight\Storage\Drivers\Contracts\FileStorageDriverContract;
use LightWeight\Storage\Drivers\LocalFileStorage;
use LightWeight\Storage\Drivers\PublicFileStorage;
use LightWeight\Exceptions\ConfigurationException;

class FileStorageDriverServiceProvider extends ServiceProvider
{
    /**
     * Proporciona definiciones para el contenedor antes de su compilación
     *
     * @return array
     */
    public function getDefinitions(): array
    {
        return [
            FileStorageDriverContract::class => \DI\factory(function () {
                $default = config("storage.default", "local");
                $driverConfig = config("storage.drivers.$default", null);

                if (!$driverConfig) {
                    throw new ConfigurationException("Storage driver configuration for '$default' not found");
                }

                $driver = $driverConfig['driver'] ?? $default;
                // Create the appropriate driver based on configuration
                return match ($driver) {
                    "disk", "local" => new LocalFileStorage(
                        $driverConfig['path'] ?? config('storage.path', rootDirectory() . '/storage/app')
                    ),                    "public" => new PublicFileStorage(
                        $driverConfig['path'] ?? config('storage.path', rootDirectory() . '/storage/public'),
                        $driverConfig['storage_uri'] ?? config('storage.storage_uri', 'storage/public'),
                        $driverConfig['url'] ?? config('storage.url', 'http://localhost')
                    ),
                    // Para S3 y FTP, necesitarías implementar estos métodos o
                    // refactorizar la lógica que estaba en registerS3Driver y registerFtpDriver
                    "s3" => throw new ConfigurationException("S3 driver not implemented in container definitions"),
                    "ftp" => throw new ConfigurationException("FTP driver not implemented in container definitions"),
                    default => throw new ConfigurationException("Unsupported storage driver: $driver")
                };
            })
        ];
    }

    /**
     * @inheritDoc
     */
    public function registerServices(Container $serviceContainer)
    {
        // Todas las definiciones ya están configuradas en getDefinitions()
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
