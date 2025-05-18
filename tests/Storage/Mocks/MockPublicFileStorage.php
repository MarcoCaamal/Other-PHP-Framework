<?php

namespace LightWeight\Tests\Storage\Mocks;

use LightWeight\Storage\Drivers\Contracts\FileStorageDriverContract;

class MockPublicFileStorage implements FileStorageDriverContract
{
    protected string $path;
    protected string $storageUri;
    protected string $url;

    public function __construct(string $path, string $storageUri, string $url)
    {
        $this->path = $path;
        $this->storageUri = $storageUri;
        $this->url = $url;
    }

    public function put(string $path, mixed $content, ?string $visibility = null): string
    {
        return $this->url($path);
    }

    public function get(string $path): mixed
    {
        return "Mock public content for {$path}";
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
        return "{$this->url}/{$this->storageUri}/{$path}";
    }

    public function size(string $path): int|false
    {
        return 2048;
    }

    public function lastModified(string $path): int|false
    {
        return time();
    }

    public function files(?string $directory = null): array
    {
        return ["public1.jpg", "public2.jpg"];
    }

    public function directories(?string $directory = null): array
    {
        return ["images", "documents"];
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
        return 'image/jpeg';
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
