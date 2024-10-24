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
    public static function singleton(string $class)
    {
        if (!array_key_exists($class, self::$instances)) {
            self::$instances[$class] = new $class();
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
