<?php

namespace LightWeight\Storage\Drivers;

use LightWeight\Storage\Drivers\Contracts\FileStorageDriverContract;

class DiskFileStorage implements FileStorageDriverContract
{
    /**
     * Directory where files should be stored.
     *
     * @var string
     */
    protected string $storageDirectory;

    /**
     * URL of the application.
     *
     * @var string
     */
    protected string $appUrl;

    /**
     * URI of the public storage directory
     *
     * @var string
     */
    protected string $storageUri;

    /**
     * Default visibility for files
     *
     * @var string
     */
    protected string $defaultVisibility;

    /**
     * Map of file paths to their visibility
     *
     * @var array
     */
    protected array $visibilityMap = [];

    /**
     * Instantiate disk file storage.
     *
     * @param string $storageDirectory
     * @param string $storageUri
     * @param string $appUrl
     * @param string $defaultVisibility
     */
    public function __construct(
        string $storageDirectory,
        string $storageUri,
        string $appUrl,
        string $defaultVisibility = 'public'
    ) {
        $this->storageDirectory = rtrim($storageDirectory, '/');
        $this->storageUri = trim($storageUri, '/');
        $this->appUrl = rtrim($appUrl, '/');
        $this->defaultVisibility = $defaultVisibility;
    }
    /**
     * {@inheritdoc}
     */
    public function put(string $path, mixed $content, ?string $visibility = null): string
    {
        $path = $this->normalizePath($path);
        $this->ensureDirectoryExists(dirname($path));

        file_put_contents($path, $content);

        // Set visibility if provided
        if ($visibility !== null) {
            $this->setVisibility($path, $visibility);
        } elseif ($this->defaultVisibility) {
            $this->setVisibility($path, $this->defaultVisibility);
        }

        $relativePath = str_replace($this->storageDirectory . '/', '', $path);
        $url = $this->url($relativePath);

        // Para cumplir con el tipo de retorno, siempre devolvemos una cadena
        // incluso si el archivo es privado
        return $url ?? "{$this->appUrl}/{$this->storageUri}/" . ltrim($relativePath, '/');
    }

    /**
     * {@inheritdoc}
     */
    public function exists(string $path): bool
    {
        return file_exists($this->normalizePath($path));
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $path): mixed
    {
        $fullPath = $this->normalizePath($path);

        if (!file_exists($fullPath)) {
            return null;
        }

        return file_get_contents($fullPath);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $path): bool
    {
        $fullPath = $this->normalizePath($path);

        if (!file_exists($fullPath)) {
            return false;
        }

        return unlink($fullPath);
    }
    /**
     * {@inheritdoc}
     */
    public function files(?string $directory = null): array
    {
        $directory = is_null($directory) ? $this->storageDirectory : $this->normalizePath($directory);

        if (!is_dir($directory)) {
            return [];
        }

        $files = [];

        // If root directory, search recursively
        if ($directory === $this->storageDirectory) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $item) {
                if ($item->isFile()) {
                    // Normalize path separators to forward slashes
                    $pathname = str_replace('\\', '/', $item->getPathname());
                    $storagePath = str_replace('\\', '/', $this->storageDirectory);

                    // Remove the storage directory prefix to get the relative path
                    // Add trailing slash to ensure we only replace at the beginning
                    $files[] = str_replace($storagePath . '/', '', $pathname);
                }
            }
        } else {
            // For specified directory, just list files in that directory
            $items = scandir($directory);

            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }

                $path = $directory . '/' . $item;

                if (is_file($path)) {
                    $files[] = $item;
                }
            }
        }

        return $files;
    }

    /**
     * {@inheritdoc}
     */
    public function directories(?string $directory = null): array
    {
        $directory = is_null($directory) ? $this->storageDirectory : $this->normalizePath($directory);

        if (!is_dir($directory)) {
            return [];
        }

        $directories = [];
        $items = scandir($directory);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory . '/' . $item;

            if (is_dir($path)) {
                if ($directory === $this->storageDirectory) {
                    // For root directory, include relative path
                    $relativePath = str_replace($this->storageDirectory . '/', '', $path);
                    $directories[] = $relativePath;
                } else {
                    // For subdirectories, just include the directory name
                    $directories[] = $item;
                }
            }
        }

        return $directories;
    }

    /**
     * {@inheritdoc}
     */
    public function size(string $path): int|false
    {
        return filesize($this->normalizePath($path));
    }

    /**
     * {@inheritdoc}
     */
    public function lastModified(string $path): int|false
    {
        return filemtime($this->normalizePath($path));
    }

    /**
     * {@inheritdoc}
     */
    public function mimeType(string $path): string|false
    {
        return mime_content_type($this->normalizePath($path));
    }    /**
     * Get the visibility of a file.
     *
     * @param string $path
     * @return string
     */
    public function getVisibility(string $path): string
    {
        $path = $this->normalizePath($path);

        // Use the tracked visibility if available
        if (isset($this->visibilityMap[$path])) {
            return $this->visibilityMap[$path];
        }

        $permissions = fileperms($path);

        if (!$permissions) {
            return $this->defaultVisibility;
        }

        // Check if the file is readable by others (world-readable)
        // File permissions in octal: 0644 (public) vs 0600 (private)
        // We check the last digit (4) which means world-readable
        $worldReadable = ($permissions & 0x0004) !== 0;

        return $worldReadable ? 'public' : 'private';
    }

    /**
     * Set the visibility of a file.
     *
     * @param string $path
     * @param string $visibility
     * @return bool
     */
    public function setVisibility(string $path, string $visibility): bool
    {
        $path = $this->normalizePath($path);

        // Default permissions: 0644 for public, 0600 for private
        $permissions = $visibility === 'public' ? 0644 : 0600;

        $this->visibilityMap[$path] = $visibility;

        return chmod($path, $permissions);
    }

    /**
     * Get the URL of a file.
     *
     * @param string $path
     * @return string|null
     */
    public function url(string $path): ?string
    {
        if ($this->getVisibility($this->normalizePath($path)) !== 'public') {
            return null;
        }

        return "{$this->appUrl}/{$this->storageUri}/" . ltrim($path, '/');
    }

    /**
     * Get the absolute path of a file.
     *
     * @param string $path
     * @return string
     */
    public function path(string $path): string
    {
        return $this->normalizePath($path);
    }

    /**
     * Normalizes a path, adding the storage directory if needed.
     *
     * @param string $path
     * @return string
     */
    protected function normalizePath(string $path): string
    {
        if (str_starts_with($path, $this->storageDirectory)) {
            return $path;
        }

        return $this->storageDirectory . '/' . ltrim($path, '/');
    }

    /**
     * Ensures that a directory exists, creating it if necessary.
     *
     * @param string $directory
     * @return void
     */
    protected function ensureDirectoryExists(string $directory): void
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    /**
     * Create a directory.
     *
     * @param string $path
     * @return bool
     */
    public function makeDirectory(string $path): bool
    {
        $path = $this->normalizePath($path);
        return is_dir($path) || mkdir($path, 0755, true);
    }

    /**
     * Delete a directory.
     *
     * @param string $directory
     * @param bool $recursive
     * @return bool
     */
    public function deleteDirectory(string $directory, bool $recursive = false): bool
    {
        $directory = $this->normalizePath($directory);

        if (!is_dir($directory)) {
            return false;
        }

        if (!$recursive) {
            // Only delete if empty
            return $this->directoryIsEmpty($directory) && rmdir($directory);
        }

        // Delete all contents recursively
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            if ($item->isDir()) {
                if (!rmdir($item->getPathname())) {
                    return false;
                }
            } else {
                if (!unlink($item->getPathname())) {
                    return false;
                }
            }
        }

        return rmdir($directory);
    }

    /**
     * Determine if a directory is empty.
     *
     * @param string $directory
     * @return bool
     */
    public function directoryIsEmpty(string $directory): bool
    {
        $directory = $this->normalizePath($directory);

        if (!is_dir($directory)) {
            return false;
        }

        $handle = opendir($directory);
        while (($entry = readdir($handle)) !== false) {
            if ($entry != "." && $entry != "..") {
                closedir($handle);
                return false;
            }
        }
        closedir($handle);

        return true;
    }

    /**
     * Copy a file from one location to another.
     *
     * @param string $from
     * @param string $to
     * @return bool
     */
    public function copy(string $from, string $to): bool
    {
        $from = $this->normalizePath($from);
        $to = $this->normalizePath($to);

        if (!file_exists($from)) {
            return false;
        }

        $this->ensureDirectoryExists(dirname($to));

        if (copy($from, $to)) {
            // Copy visibility as well
            $permissions = fileperms($from);
            return chmod($to, $permissions);
        }

        return false;
    }

    /**
     * Move a file from one location to another.
     *
     * @param string $from
     * @param string $to
     * @return bool
     */
    public function move(string $from, string $to): bool
    {
        $from = $this->normalizePath($from);
        $to = $this->normalizePath($to);

        if (!file_exists($from)) {
            return false;
        }

        $this->ensureDirectoryExists(dirname($to));

        return rename($from, $to);
    }
}
