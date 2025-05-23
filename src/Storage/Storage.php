<?php

namespace LightWeight\Storage;

use LightWeight\Storage\StorageManager;
use LightWeight\Storage\Drivers\Contracts\FileStorageDriverContract;

/**
 * File storage utilities.
 */
class Storage
{
    /**
     * Get a storage driver instance or the default driver
     *
     * @param string|null $driver
     * @return FileStorageDriverContract
     */
    public static function driver(?string $driver = null): FileStorageDriverContract
    {
        return StorageManager::driver($driver);
    }

    /**
     * Put file in the storage directory.
     *
     * @param string $path
     * @param mixed $content
     * @param string|null $driver
     * @return string URL of the file.
     */
    public static function put(string $path, mixed $content, ?string $driver = null): string
    {
        return static::driver($driver)->put($path, $content);
    }

    /**
     * Check if a file exists.
     *
     * @param string $path
     * @param string|null $driver
     * @return bool
     */
    public static function exists(string $path, ?string $driver = null): bool
    {
        return static::driver($driver)->exists($path);
    }

    /**
     * Get file content.
     *
     * @param string $path
     * @param string|null $driver
     * @return mixed
     */
    public static function get(string $path, ?string $driver = null): mixed
    {
        return static::driver($driver)->get($path);
    }

    /**
     * Delete a file.
     *
     * @param string $path
     * @param string|null $driver
     * @return bool
     */
    public static function delete(string $path, ?string $driver = null): bool
    {
        return static::driver($driver)->delete($path);
    }

    /**
     * Lists all files in a directory.
     *
     * @param string|null $directory
     * @param string|null $driver
     * @return array
     */
    public static function files(?string $directory = null, ?string $driver = null): array
    {
        return static::driver($driver)->files($directory);
    }

    /**
     * Lists all directories in a directory.
     *
     * @param string|null $directory
     * @param string|null $driver
     * @return array
     */
    public static function directories(?string $directory = null, ?string $driver = null): array
    {
        return static::driver($driver)->directories($directory);
    }

    /**
     * Get file size in bytes.
     *
     * @param string $path
     * @param string|null $driver
     * @return int|false
     */
    public static function size(string $path, ?string $driver = null): int|false
    {
        return static::driver($driver)->size($path);
    }

    /**
     * Get the file's last modification time.
     *
     * @param string $path
     * @param string|null $driver
     * @return int|false
     */
    public static function lastModified(string $path, ?string $driver = null): int|false
    {
        return static::driver($driver)->lastModified($path);
    }

    /**
     * Get file mime type.
     *
     * @param string $path
     * @param string|null $driver
     * @return string|false
     */
    public static function mimeType(string $path, ?string $driver = null): string|false
    {
        return static::driver($driver)->mimeType($path);
    }

    /**
     * Store file with the provided visibility.
     *
     * @param string $path
     * @param mixed $content
     * @param string $visibility
     * @param string|null $driver
     * @return string
     */
    public static function putWithVisibility(string $path, mixed $content, string $visibility, ?string $driver = null): string
    {
        return static::driver($driver)->put($path, $content, $visibility);
    }

    /**
     * Store file as private.
     *
     * @param string $path
     * @param mixed $content
     * @param string|null $driver
     * @return string
     */
    public static function putPrivate(string $path, mixed $content, ?string $driver = null): string
    {
        return static::putWithVisibility($path, $content, 'private', $driver);
    }

    /**
     * Store file as public.
     *
     * @param string $path
     * @param mixed $content
     * @param string|null $driver
     * @return string
     */
    public static function putPublic(string $path, mixed $content, ?string $driver = null): string
    {
        return static::putWithVisibility($path, $content, 'public', $driver);
    }

    /**
     * Get the URL of a file.
     *
     * @param string $path
     * @param string|null $driver
     * @return string|null
     */
    public static function url(string $path, ?string $driver = null): ?string
    {
        return static::driver($driver)->url($path);
    }

    /**
     * Get the absolute path of a file.
     *
     * @param string $path
     * @param string|null $driver
     * @return string
     */
    public static function path(string $path, ?string $driver = null): string
    {
        return static::driver($driver)->path($path);
    }

    /**
     * Get the visibility of a file.
     *
     * @param string $path
     * @param string|null $driver
     * @return string
     */
    public static function getVisibility(string $path, ?string $driver = null): string
    {
        return static::driver($driver)->getVisibility($path);
    }

    /**
     * Set the visibility of a file.
     *
     * @param string $path
     * @param string $visibility
     * @param string|null $driver
     * @return bool
     */
    public static function setVisibility(string $path, string $visibility, ?string $driver = null): bool
    {
        return static::driver($driver)->setVisibility($path, $visibility);
    }

    /**
     * Make a file public.
     *
     * @param string $path
     * @param string|null $driver
     * @return bool
     */
    public static function makePublic(string $path, ?string $driver = null): bool
    {
        return static::setVisibility($path, 'public', $driver);
    }

    /**
     * Make a file private.
     *
     * @param string $path
     * @param string|null $driver
     * @return bool
     */
    public static function makePrivate(string $path, ?string $driver = null): bool
    {
        return static::setVisibility($path, 'private', $driver);
    }

    /**
     * Determine if a directory is empty.
     *
     * @param string $directory
     * @param string|null $driver
     * @return bool
     */
    public static function directoryIsEmpty(string $directory, ?string $driver = null): bool
    {
        return count(static::files($directory, $driver)) === 0 &&
               count(static::directories($directory, $driver)) === 0;
    }

    /**
     * Create a directory.
     *
     * @param string $path
     * @param string|null $driver
     * @return bool
     */
    public static function makeDirectory(string $path, ?string $driver = null): bool
    {
        $fullPath = static::driver($driver)->path($path);
        return is_dir($fullPath) || mkdir($fullPath, 0755, true);
    }

    /**
     * Delete a directory.
     *
     * @param string $directory
     * @param bool $recursive
     * @param string|null $driver
     * @return bool
     */
    public static function deleteDirectory(string $directory, bool $recursive = false, ?string $driver = null): bool
    {
        $success = true;

        if ($recursive) {
            // Delete all files in directory
            foreach (static::files($directory, $driver) as $file) {
                if (!static::delete($directory . '/' . $file, $driver)) {
                    $success = false;
                }
            }

            // Delete all subdirectories
            foreach (static::directories($directory, $driver) as $dir) {
                if (!static::deleteDirectory($directory . '/' . $dir, true, $driver)) {
                    $success = false;
                }
            }
        } else {
            // Only delete if empty
            if (!static::directoryIsEmpty($directory, $driver)) {
                return false;
            }
        }

        // Delete the directory itself
        $fullPath = static::driver($driver)->path($directory);
        return $success && (is_dir($fullPath) ? rmdir($fullPath) : true);
    }

    /**
     * Copy a file from one location to another.
     *
     * @param string $from
     * @param string $to
     * @param string|null $fromDriver
     * @param string|null $toDriver
     * @return bool
     */
    public static function copy(
        string $from,
        string $to,
        ?string $fromDriver = null,
        ?string $toDriver = null
    ): bool {
        $content = static::get($from, $fromDriver);

        if ($content === null) {
            return false;
        }

        $visibility = static::getVisibility($from, $fromDriver);
        static::putWithVisibility($to, $content, $visibility, $toDriver);

        return true;
    }

    /**
     * Move a file from one location to another.
     *
     * @param string $from
     * @param string $to
     * @param string|null $fromDriver
     * @param string|null $toDriver
     * @return bool
     */
    public static function move(
        string $from,
        string $to,
        ?string $fromDriver = null,
        ?string $toDriver = null
    ): bool {
        // If same driver, try to use rename for better performance
        if ($fromDriver === $toDriver) {
            $fromPath = static::driver($fromDriver)->path($from);
            $toPath = static::driver($toDriver)->path($to);

            // Ensure target directory exists
            $toDir = dirname($toPath);
            if (!is_dir($toDir)) {
                mkdir($toDir, 0755, true);
            }

            // Try to rename the file
            if (@rename($fromPath, $toPath)) {
                return true;
            }
        }

        // Fall back to copy and delete
        if (static::copy($from, $to, $fromDriver, $toDriver)) {
            return static::delete($from, $fromDriver);
        }

        return false;
    }

    /**
     * Create a symbolic link from the target file to the link path.
     * This is particularly useful for making a private file accessible publicly.
     *
     * @param string $target Existing file path
     * @param string $link Path for the symbolic link
     * @param string|null $targetDriver Driver where the target file exists
     * @param string|null $linkDriver Driver where to create the link
     * @return bool
     */
    public static function symlink(
        string $target,
        string $link,
        ?string $targetDriver = null,
        ?string $linkDriver = null
    ): bool {
        $targetPath = static::driver($targetDriver)->path($target);
        $linkPath = static::driver($linkDriver)->path($link);

        // Ensure the directory for the link exists
        $linkDir = dirname($linkPath);
        if (!is_dir($linkDir)) {
            mkdir($linkDir, 0755, true);
        }

        // Remove existing link or file if it exists
        if (file_exists($linkPath) || is_link($linkPath)) {
            if (is_link($linkPath)) {
                unlink($linkPath);
            } else {
                return false; // Don't overwrite a real file
            }
        }

        return symlink($targetPath, $linkPath);
    }

    /**
     * Check if a path is a symbolic link.
     *
     * @param string $path
     * @param string|null $driver
     * @return bool
     */
    public static function isSymlink(string $path, ?string $driver = null): bool
    {
        return is_link(static::driver($driver)->path($path));
    }

    /**
     * Get the target of a symbolic link.
     *
     * @param string $path
     * @param string|null $driver
     * @return string|false
     */
    public static function readlink(string $path, ?string $driver = null): string|false
    {
        return readlink(static::driver($driver)->path($path));
    }
}
