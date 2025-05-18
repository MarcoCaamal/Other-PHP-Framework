<?php

namespace LightWeight\Tests\Storage\Mocks;

use LightWeight\Storage\Drivers\Contracts\FileStorageDriverContract;

class MockLocalFileStorage implements FileStorageDriverContract
{
    protected string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function put(string $path, mixed $content, ?string $visibility = null): string
    {
        return $this->url($path);
    }

    public function get(string $path): mixed
    {
        return "Mock content for {$path}";
    }

    public function exists(string $path): bool
    {
        return true;
    }

    public function delete(string $path): bool
    {
        return true;
    }

    public function url(string $path): ?string
    {
        return "local://{$path}";
    }

    public function size(string $path): int|false
    {
        return 1024;
    }

    public function lastModified(string $path): int|false
    {
        return time();
    }

    public function files(?string $directory = null): array
    {
        return ["file1.txt", "file2.txt"];
    }

    public function directories(?string $directory = null): array
    {
        return ["dir1", "dir2"];
    }

    public function makeDirectory(string $directory): bool
    {
        return true;
    }

    public function deleteDirectory(string $directory, bool $recursive = false): bool
    {
        return true;
    }
    
    public function path(string $path): string
    {
        return $this->path . '/' . ltrim($path, '/');
    }
    
    public function mimeType(string $path): string|false
    {
        return 'text/plain';
    }
    
    public function getVisibility(string $path): string
    {
        return 'public';
    }
    
    public function setVisibility(string $path, string $visibility): bool
    {
        return true;
    }
    
    public function directoryIsEmpty(string $directory): bool
    {
        return false;
    }
    
    public function copy(string $from, string $to): bool
    {
        return true;
    }
    
    public function move(string $from, string $to): bool
    {
        return true;
    }
}
