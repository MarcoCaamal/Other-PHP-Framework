<?php

namespace LightWeight\Container;

use DI\Container as DIContainer;
use DI\ContainerBuilder;
use LightWeight\Container\Exceptions\ContainerNotBuildException;

class Container
{
    private static ?DIContainer $instance = null;
    private function __construct()
    {
    }
    public static function getInstance()
    {
        if(self::$instance === null) {
            $builder = new ContainerBuilder();
            // if(env('', 'dev') === 'prod') {
            //     $builder->enableCompilation(__DIR__ . '/tmp');
            //     $builder->writeProxiesToFile(true, __DIR__ . '/tmp/proxies');
            // }
            self::$instance = $builder->build();
        }
        return self::$instance;
    }
    public static function deleteInstance()
    {
        self::$instance = null;
    }
    /**
     * @template T
     * @param class-string<T>|string $id
     * @throws \LightWeight\Container\Exceptions\ContainerNotBuildException
     * @return T
     */
    public static function get(string $id)
    {
        if(self::$instance === null) {
            throw new ContainerNotBuildException();
        }
        return self::$instance->get($id);
    }
    public static function set(string $id, mixed $value)
    {
        if(self::$instance === null) {
            throw new ContainerNotBuildException();
        }
        self::$instance->set($id, $value);
    }
    public static function has(string $id): bool
    {
        if(self::$instance === null) {
            throw new ContainerNotBuildException();
        }
        return self::$instance->has($id);
    }
    public static function call(array|string|callable $id, array $parameters = []): mixed
    {
        if(self::$instance === null) {
            throw new ContainerNotBuildException();
        }
        return self::$instance->call($id, $parameters);
    }
    public static function make(string $id, array $parameters = []): mixed
    {
        if(self::$instance === null) {
            throw new ContainerNotBuildException();
        }
        return self::$instance->make($id, $parameters);
    }
    public static function getContainer(): DIContainer
    {
        if(self::$instance === null) {
            throw new ContainerNotBuildException();
        }
        return self::$instance;
    }
}
