<?php

use LightWeight\App;
use LightWeight\Config\Config;
use LightWeight\Container\Container;

use function PHPSTORM_META\type;

/**
 *
 * @template T
 * @param class-string<T> $class
 * @return T
 */
function app(string $class = App::class)
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
    return Config::get($configuration, $default);
}
function resourcesDirectory(): string
{
    return App::$root . "/resources";
}
function rootDirectory(): string
{
    return App::$root;
}
function debugDie($var)
{
    echo var_dump($var);
    die;
}
