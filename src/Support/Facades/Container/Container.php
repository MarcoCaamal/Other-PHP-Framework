<?php 

namespace LightWeight\Support\Facades\Container;

class Container
{
    public static function get(string $id)
    {
        return \LightWeight\Container\Container::getInstance()->get($id);
    }
    public static function set(string $id, mixed $value)
    {
        return \LightWeight\Container\Container::getInstance()->set($id, $value);
    }
    public static function has(string $id): bool
    {
        return \LightWeight\Container\Container::getInstance()->has($id);
    }
    public static function make(string $id, array $parameters = [])
    {
        return \LightWeight\Container\Container::getInstance()->make($id, $parameters);
    }
    public static function call(array|string|callable $id, array $parameters = []): mixed
    {
        return \LightWeight\Container\Container::getInstance()->call($id, $parameters);
    }
}