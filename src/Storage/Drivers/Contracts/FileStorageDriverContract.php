<?php

namespace LightWeight\Storage\Drivers\Contracts;

interface FileStorageDriverContract
{
    /**
     * Store file.
     *
     * @param string $path
     * @param mixed $content
     * @param string|null $visibility
     * @return string The URL of the stored file.
     */
    public function put(string $path, mixed $content, ?string $visibility = null): string;

    /**
     * Check if a file exists.
     *
     * @param string $path
     * @return bool
     */
    public function exists(string $path): bool;

    /**
     * Get file content.
     *
     * @param string $path
     * @return mixed
     */
    public function get(string $path): mixed;

    /**
     * Delete a file.
     *
     * @param string $path
     * @return bool
     */
    public function delete(string $path): bool;

    /**
     * Lists all files in a directory.
     *
     * @param string|null $directory
     * @return array
     */
    public function files(?string $directory = null): array;

    /**
     * Lists all directories in a directory.
     *
     * @param string|null $directory
     * @return array
     */
    public function directories(?string $directory = null): array;

    /**
     * Get file size in bytes.
     *
     * @param string $path
     * @return int|false
     */
    public function size(string $path): int|false;

    /**
     * Get the file's last modification time.
     *
     * @param string $path
     * @return int|false
     */
    public function lastModified(string $path): int|false;

    /**
     * Get file mime type.
     *
     * @param string $path
     * @return string|false
     */
    public function mimeType(string $path): string|false;

    /**
     * Get the visibility of a file.
     *
     * @param string $path
     * @return string
     */
    public function getVisibility(string $path): string;

    /**
     * Set the visibility of a file.
     *
     * @param string $path
     * @param string $visibility
     * @return bool
     */
    public function setVisibility(string $path, string $visibility): bool;

    /**
     * Get the URL of a file.
     *
     * @param string $path
     * @return string|null
     */
    public function url(string $path): ?string;

    /**
     * Get the absolute path of a file.
     *
     * @param string $path
     * @return string
     */
    public function path(string $path): string;

    /**
     * Create a directory.
     *
     * @param string $path
     * @return bool
     */
    public function makeDirectory(string $path): bool;

    /**
     * Delete a directory.
     *
     * @param string $directory
     * @param bool $recursive
     * @return bool
     */
    public function deleteDirectory(string $directory, bool $recursive = false): bool;

    /**
     * Determine if a directory is empty.
     *
     * @param string $directory
     * @return bool
     */
    public function directoryIsEmpty(string $directory): bool;

    /**
     * Copy a file from one location to another.
     *
     * @param string $from
     * @param string $to
     * @return bool
     */
    public function copy(string $from, string $to): bool;

    /**
     * Move a file from one location to another.
     *
     * @param string $from
     * @param string $to
     * @return bool
     */
    public function move(string $from, string $to): bool;
}
