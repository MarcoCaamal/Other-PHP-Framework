<?php

namespace LightWeight\Config;

use function DI\string;

class Config
{
    /**
     * Data configuration
     * @var array
     */
    public array $config = [];
    /**
     * Load configuration from a directory
     * @param string $path
     * @return void
     */
    public function loadFromDirectory(string $path, string|array|null $exclude = null)
    {
        foreach(glob("$path/*.php") as $config) {
            if (is_array($exclude) && in_array(basename($config), $exclude)) {
                continue;
            }
            if(is_string($exclude) && $exclude === basename($config)) {
                continue;
            }
            $key = explode('.', basename($config))[0];
            $values = require_once $config;
            $this->config[$key] = $values;
        }
    }
    /**
     * Load configuration from a file
     * @param string $path
     * @return void
     */
    public function loadFromFile(string $path)
    {
        if (!file_exists($path)) {
            throw new \Exception("File not found: $path");
        }
        $key = explode('.', basename($path))[0];
        $values = require_once $path;
        $this->config[$key] = $values;
    }
    /**
     * Get configuration value
     * @param string $configuration
     * @param mixed $default
     * @return mixed
     */
    public function get(string $configuration, $default = null)
    {
        $keys = explode(".", $configuration);
        $finalKey = array_pop($keys);
        $array = $this->config;
        foreach ($keys as $key) {
            if (!array_key_exists($key, $array)) {
                return $default;
            }
            $array = $array[$key];
        }
        return $array[$finalKey] ?? $default;
    }
    /**
     * Set configuration value
     * @param string $configuration
     * @param mixed $value
     * @return void
     */
    public function set(string $configuration, $value): void
    {
        $keys = explode(".", $configuration);
        $finalKey = array_pop($keys);
        $array = &$this->config;
        foreach ($keys as $key) {
            if (!array_key_exists($key, $array)) {
                $array[$key] = [];
            }
            $array = &$array[$key];
        }
        $array[$finalKey] = $value;
    }
}
