<?php

namespace LightWeight\Storage;

use LightWeight\Storage\Drivers\Contracts\FileStorageDriverContract;

/**
 * File storage utilities.
 */
class Storage
{
    /**
     * Put file in the storage directory.
     *
     * @param string $path
     * @param mixed $content
     * @return string URL of the file.
     */
    public static function put(string $path, mixed $content): string
    {
        return app(FileStorageDriverContract::class)->put($path, $content);
    }
}
