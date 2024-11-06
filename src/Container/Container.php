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
            $builder->useAutowiring(false);
            if(env('', 'dev') === 'prod') {
                $builder->enableCompilation(__DIR__ . '/tmp');
                $builder->writeProxiesToFile(true, __DIR__ . '/tmp/proxies');
            }
            self::$instance = $builder->build();
        }
        return self::$instance;
    }
    public static function deleteInstance()
    {
        self::$instance = null;
    }
}
