<?php

use LightWeight\Application;
use LightWeight\Config\Config;
use Psr\Container\ContainerInterface;

return [
    Config::class => \DI\factory(function ($rootPath = null) {
        $config = new Config();
        $path = $rootPath ?? dirname(__DIR__, 4) . '/config';
        $config->loadFromDirectory($path);
        return $config;
    })->parameter('rootPath', \DI\get('app.root')),
];