<?php

use OtherPHPFramework\App;
use OtherPHPFramework\Container\Container;

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
function singleton(string $class)
{
    return Container::singleton($class);
}
function debugDie($var)
{
    echo json_encode($var);
    die;
}
