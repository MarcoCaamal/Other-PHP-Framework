<?php

use SMFramework\App;
use SMFramework\Container\Container;

/**
 *
 * @template T
 * @param class-string<T> $class
 * @return T
 */
function app(string $class = App::class)
{
    return Container::resolve($class);
}
/**
 *
 * @template T
 * @param class-string<T> $class
 * @return T
 */
function singleton(string $class, string|callable|null $build)
{
    return Container::singleton($class, $build);
}
function debugDie($var)
{
    echo json_encode($var);
    die;
}
