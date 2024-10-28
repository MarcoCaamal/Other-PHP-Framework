<?php

namespace SMFramework\Container;

class Container
{
    private static array $instances = [];

    /**
     * @template T
     * @param class-string<T> $class
     *
     * @return T
     */
    public static function singleton(string $class, string|callable|null $build = null)
    {
        if (!array_key_exists($class, self::$instances)) {
            match (true) {
                is_null($build) => self::$instances[$class] = new $class(),
                is_string($build) => self::$instances[$class] = new $build(),
                is_callable($build) => self::$instances[$class] = $build(),
            };
        }

        return self::$instances[$class];
    }

    /**
     * @template T
     * @param class-string<T> $class
     *
     * @return T
     */
    public static function resolve(string $class)
    {
        return self::$instances[$class] ?? null;
    }
}
