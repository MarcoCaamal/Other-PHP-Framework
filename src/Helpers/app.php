<?php

use SMFramework\App;
use SMFramework\Config\Config;
use SMFramework\Container\Container;

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
function singleton(string $class, string|callable|null $build = null)
{
    return Container::singleton($class, $build);
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
