<?php

namespace LightWeight\Container;

use DI\Container as DIContainer;
use DI\ContainerBuilder;
use LightWeight\Container\Exceptions\ContainerNotBuildException;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    protected static ?Container $instance = null;
    protected static ?ContainerBuilder $builder = null;
    private ?DIContainer $container = null;
    public static function getInstance(): Container
    {
        if (self::$instance === null) {
            throw new ContainerNotBuildException();
        }
        return self::$instance;
    }
    public function __construct()
    {
        self::$builder = new ContainerBuilder();
        self::$builder->addDefinitions(__DIR__ . '/app-container.php');
    }
    /**
     * Build the container
     * @return void
     */
    public function build(): void
    {
        if (self::$instance === null) {
            self::$instance = $this;
            $this->container = self::$builder->build();
        }
    }
    public function addDefinitions(string|array $definitions): void
    {
        if (self::$builder === null) {
            throw new ContainerNotBuildException();
        }
        self::$builder->addDefinitions($definitions);
    }
    public function enableCache(string $cachePath): void
    {
        if (self::$builder === null) {
            throw new ContainerNotBuildException();
        }
        self::$builder->enableCompilation($cachePath);
        self::$builder->writeProxiesToFile(true, $cachePath . '/proxies');
    }
    public function disableCache(): void
    {
        if (self::$builder === null) {
            throw new ContainerNotBuildException();
        }
        self::$builder->enableCompilation(false);
        self::$builder->writeProxiesToFile(false, '');
    }
    public function enableAutowiring(): void
    {
        if (self::$builder === null) {
            throw new ContainerNotBuildException();
        }
        self::$builder->useAutowiring(true);
    }
    public function disableAutowiring(): void
    {
        if (self::$builder === null) {
            throw new ContainerNotBuildException();
        }
        self::$builder->useAutowiring(false);
    }
    /**
     * @template T
     * @param class-string<T>|string $id
     * @throws \LightWeight\Container\Exceptions\ContainerNotBuildException
     * @return T
     */
    public function get(string $id)
    {
        if($this->container === null) {
            throw new ContainerNotBuildException();
        }
        return $this->container->get($id);
    }
    public function set(string $id, mixed $value)
    {
        if($this->container === null) {
            throw new ContainerNotBuildException();
        }
        $this->container->set($id, $value);
    }
    public function has(string $id): bool
    {
        if($this->container === null) {
            throw new ContainerNotBuildException();
        }
        return $this->container->has($id);
    }
    public function call(array|string|callable $id, array $parameters = []): mixed
    {
        if($this->container === null) {
            throw new ContainerNotBuildException();
        }
        return $this->container->call($id, $parameters);
    }
    public function make(string $id, array $parameters = []): mixed
    {
        if($this->container === null) {
            throw new ContainerNotBuildException();
        }
        return $this->container->make($id, $parameters);
    }
    public function getContainer(): DIContainer
    {
        if($this->container === null) {
            throw new ContainerNotBuildException();
        }
        return $this->container;
    }
}
