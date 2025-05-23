<?php

use LightWeight\Application;
use LightWeight\Config\Config;
use LightWeight\Container\Container;

use function PHPSTORM_META\type;

/**
 *
 * @template T
 * @param class-string<T> $class
 * @return T
 */
function app(string $class = Application::class)
{
    $resolved = Container::getInstance()->get($class);
    return $resolved;
}
/**
 *
 * @template T
 * @param class-string<T> $class
 * @return T
 */
function singleton(string $class, string|callable|object|null $build = null)
{
    $container = Container::getInstance();
    if($container->has($class)) {
        return $container->get($class);
    }
    match(true) {
        is_null($build) => $container->set($class, \DI\create($class)),
        is_string($build) => $container->set($class, \DI\create($build)),
        is_object($build) && !$build instanceof \Closure => $container->set($class, $build),
        is_callable($build) => $container->set($class, $build)
    };
    return $container->get($class);
}

/**
 * Obtiene una nueva instancia (transient) del contenedor
 * 
 * @template T
 * @param class-string<T> $class
 * @return T
 */
function make(string $class)
{
    return Container::getInstance()->make($class);
}

function env(string $variable, $default = null)
{
    return $_ENV[$variable] ?? $default;
}
function config(string $configuration, $default = null)
{
    return Container::getInstance()->get(Config::class)->get($configuration, $default);
}
function resourcesDirectory(): string
{
    return Application::$root . "/resources";
}
function rootDirectory(): string
{
    return Application::$root;
}
/**
 * Get the path to the storage directory.
 *
 * @param  string  $path
 * @return string
 */
function storagePath(string $path = ''): string
{
    return Application::$root . '/storage' . ($path ? '/' . ltrim($path, '/') : '');
}
function debugDie($var)
{
    echo var_dump($var);
    die;
}

/**
 * Get the path to the public directory.
 *
 * @param  string  $path
 * @return string
 */
function publicPath(string $path = ''): string
{
    return Application::$root . '/public' . ($path ? '/' . ltrim($path, '/') : '');
}
