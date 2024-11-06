<?php

namespace LightWeight\Container;

use DI\Container as DIContainer;
use DI\ContainerBuilder;

class Container
{
    private static ?DIContainer $instance;
    private function __construct() {}
    public static function getInstance()
    {
        if(self::$instance === null) {
            self::buildContainer();
        }
        return self::$instance;
    }
    private static function buildContainer()
    {
        $builder = new ContainerBuilder();
    }
}
