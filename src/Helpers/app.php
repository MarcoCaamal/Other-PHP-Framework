<?php

use LightWeight\App;
use LightWeight\Config\Config;
use LightWeight\Container\Container;

use function PHPSTORM_META\type;

/**
 *
 * @template T
 * @param class-string<T> $class
 * @return T
 */
function app(string $class = App::class)
{
    $resolved = Container::getInstance()->get($class);
    return $resolved;
}
/**
 *
 * @template T
 * @param class-string<T> $class
 * @return T
 */
function singleton(string $class, string|callable|object|null $build = null)
{
    $container = Container::getInstance();
    if($container->has($class)) {
        return $container->get($class);
    }
    match(true) {
        is_null($build) => $container->set($class, \DI\create($class)),
        is_string($build) => $container->set($class, \DI\create($build)),
        is_object($build) && !$build instanceof \Closure => $container->set($class, $build),
        is_callable($build) => $container->set($class, $build)
    };
    return $container->get($class);
}
function env(string $variable, $default = null)
{
    return $_ENV[$variable] ?? $default;
}
function config(string $configuration, $default = null)
{
    return Config::get($configuration, $default);
}
function resourcesDirectory(): string
{
    return App::$root . "/resources";
}
function debugDie($var)
{
    echo json_encode($var);
    die;
}
function php_exec($cmd)
{

    if(function_exists('exec')) {
        $output = array();
        $return_var = 0;
        exec($cmd, $output, $return_var);
        return implode(" ", array_values($output));
    } elseif(function_exists('shell_exec')) {
        return shell_exec($cmd);
    } elseif(function_exists('system')) {
        $return_var = 0;
        return system($cmd, $return_var);
    } elseif(function_exists('passthru')) {
        $return_var = 0;
        ob_start();
        passthru($cmd, $return_var);
        $output = ob_get_contents();
        ob_end_clean(); //Use this instead of ob_flush()
        return $output;
    } elseif(function_exists('proc_open')) {
        $proc = proc_open(
            $cmd,
            array(
                array("pipe","r"),
                array("pipe","w"),
                array("pipe","w")
            ),
            $pipes
        );
        return stream_get_contents($pipes[1]);
    } else {
        return "@PHP_COMMAND_NOT_SUPPORT";
    }

}
