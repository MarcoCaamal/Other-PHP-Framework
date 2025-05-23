<?php

namespace LightWeight\Storage\Drivers;

use LightWeight\Storage\Drivers\DiskFileStorage;

/**
 * Public file storage driver.
 * For public files that always have a valid URL.
 */
class PublicFileStorage extends DiskFileStorage
{
    /**
     * Instantiate public file storage.
     *
     * @param string $storageDirectory
     * @param string $storageUri
     * @param string $appUrl
     */
    public function __construct(string $storageDirectory, string $storageUri, string $appUrl)
    {
        parent::__construct(
            $storageDirectory,
            $storageUri,
            $appUrl,
            'public' // Default visibility is always public
        );
    }

    /**
     * {@inheritdoc}
     */
    public function url(string $path): ?string
    {
        // Public storage always returns a URL regardless of visibility
        return "{$this->appUrl}/{$this->storageUri}/" . ltrim($path, '/');
    }

    /**
     * {@inheritdoc}
     */
    public function put(string $path, mixed $content, ?string $visibility = null): string
    {
        $path = $this->normalizePath($path);
        $this->ensureDirectoryExists(dirname($path));

        file_put_contents($path, $content);

        // Always set to public regardless of requested visibility
        $this->setVisibility($path, 'public');

        return $this->url(str_replace($this->storageDirectory . '/', '', $path));
    }
}
