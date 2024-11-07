<?php

namespace LightWeight\Storage\Drivers\Contracts;

interface FileStorageDriverContract
{
    /**
     * Store file.
     *
     * @param string $path
     * @param mixed $content
     * @return string The URL of the stored file.
     */
    public function put(string $path, mixed $content);
}
