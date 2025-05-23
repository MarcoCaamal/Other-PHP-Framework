<?php

namespace LightWeight\Storage\Drivers;

use LightWeight\Storage\Drivers\DiskFileStorage;

/**
 * Local file storage driver.
 * For private files not accessible via URL.
 */
class LocalFileStorage extends DiskFileStorage
{
    /**
     * Instantiate local file storage.
     *
     * @param string $storageDirectory
     */
    public function __construct(string $storageDirectory)
    {
        parent::__construct(
            $storageDirectory,
            '', // storageUri not needed for local storage
            '', // appUrl not needed for local storage
            'private' // Default visibility is private
        );
    }

    /**
     * {@inheritdoc}
     */
    public function url(string $path): ?string
    {
        // Local storage has no URL access
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function put(string $path, mixed $content, ?string $visibility = null): string
    {
        $path = $this->normalizePath($path);
        $this->ensureDirectoryExists(dirname($path));

        file_put_contents($path, $content);

        // Set visibility if provided, otherwise use private
        $this->setVisibility($path, $visibility ?? 'private');

        // Return the path instead of URL for local storage
        return str_replace($this->storageDirectory . '/', '', $path);
    }

    /**
     * {@inheritdoc}
     * Override to always return 'private' for LocalFileStorage
     */
    public function getVisibility(string $path): string
    {
        return 'private';
    }
}
