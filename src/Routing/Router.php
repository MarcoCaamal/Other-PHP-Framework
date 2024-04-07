<?php

namespace Junk\Routing;

use Junk\Http\HttpMethod;
use Junk\Http\Request;
use Junk\Http\HttpNotFoundException;

/**
 * HTTP Router
 */
class Router
{
    /**
     * HTTP routes
     * @var array<string, Route[]>
     */
    protected array $routes = [];
    /**
     * Create a new router
     */
    public function __construct()
    {
        foreach (HttpMethod::cases() as $method) {
            $this->routes[$method->value] = [];
        }
    }
    /**
     * Register a new route with the given `#method` and `$uri`
     *
     * @param \Junk\Http\HttpMethod $method
     * @param string $uri
     * @param \Closure $action
     * @return void
     */
    protected function registerRoute(HttpMethod $method, string $uri, \Closure $action)
    {
        $this->routes[$method->value][] = new Route($uri, $action);
    }
    /**
     * Resolve the route of the `$request`
     *
     * @param \Junk\Http\Request $request
     * @throws \Junk\Http\HttpNotFoundException
     * @return \Junk\Routing\Route
     */
    public function resolve(Request $request): Route
    {
        foreach ($this->routes[$request->method()->value] as $route) {
            if ($route->matches($request->uri())) {
                return $route;
            }
        }
        throw new HttpNotFoundException();
    }

    /**
     * Register a GET route with the give `$uri` and `$action`
     *
     * @param string $uri
     * @param \Closure $action
     * @return void
     */
    public function get(string $uri, \Closure $action)
    {
        $this->registerRoute(HttpMethod::GET, $uri, $action);
    }

    /**
     * Register a POST route with the given `$uri` and `$action`
     *
     * @param string $uri
     * @param \Closure $action
     * @return void
     */
    public function post(string $uri, \Closure $action)
    {
        $this->registerRoute(HttpMethod::POST, $uri, $action);
    }

    /**
     * Register a PUT route with the given `$uri` and `$action`
     *
     * @param string $uri
     * @param \Closure $action
     * @return void
     */
    public function put(string $uri, \Closure $action)
    {
        $this->registerRoute(HttpMethod::PUT, $uri, $action);
    }

    /**
     * Register a DELETE route with the given `$uri` and `$action`
     *
     * @param string $uri
     * @param \Closure $action
     * @return void
     */
    public function delete(string $uri, \Closure $action)
    {
        $this->registerRoute(HttpMethod::DELETE, $uri, $action);
    }

    /**
     * Register a PATCH route with the given `$uri` and `$action`
     *
     * @param string $uri
     * @param \Closure $action
     * @return void
     */
    public function patch(string $uri, \Closure $action)
    {
        $this->registerRoute(HttpMethod::PATCH, $uri, $action);
    }
}
