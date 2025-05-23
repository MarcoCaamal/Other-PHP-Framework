<?php

use LightWeight\Container\Container;
use LightWeight\Routing\Router;
use LightWeight\Routing\Route;
use LightWeight\Config\Config;

/**
 * Generate a URL for a named route
 *
 * @param string $name The name of the route
 * @param array $parameters Route parameters to replace in the URI
 * @param bool $absolute Whether to generate an absolute URL
 * @param string|null $domain Optional domain for absolute URLs
 * @return string|null The generated URL or null if route not found
 */
function route(string $name, array $parameters = [], bool $absolute = false, ?string $domain = null): ?string
{
    $router = Container::getInstance()->get(Router::class);

    if ($absolute) {
        return $router->generateAbsoluteUrl($name, $parameters, $domain);
    }

    return $router->generateUrl($name, $parameters);
}

/**
 * Get a route by its name
 *
 * @param string $name The name of the route
 * @return Route|null The route if found, null otherwise
 */
function getRouteByName(string $name): ?Route
{
    $router = Container::getInstance()->get(Router::class);
    return $router->getRouteByName($name);
}
