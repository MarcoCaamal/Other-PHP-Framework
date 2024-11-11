<?php

namespace LightWeight\Routing;

use LightWeight\Container\DependencyInjection;
use LightWeight\Http\HttpMethod;
use LightWeight\Http\Request;
use LightWeight\Http\HttpNotFoundException;
use LightWeight\Http\Response;
use LightWeight\Routing\Exception\RouteDuplicatedNameException;
use LightWeight\Routing\Route;

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
     * @param \LightWeight\Http\HttpMethod $method
     * @param string $uri
     * @param \Closure $action
     * @return Route
     */
    protected function registerRoute(HttpMethod $method, string $uri, \Closure|array $action): Route
    {
        $route = new Route($uri, $action);
        $this->verifyIfExistsRouteWithDuplicatedName($route);
        $this->routes[$method->value][] = $route;
        return $route;
    }
    /**
     * Resolve the route of the `$request`
     *
     * @param \LightWeight\Http\Request $request
     * @throws \LightWeight\Http\HttpNotFoundException
     * @return \LightWeight\Routing\Route
     */
    public function resolveRoute(Request $request): Route
    {
        foreach ($this->routes[$request->method()->value] as $route) {
            if ($route->matches($request->uri())) {
                return $route;
            }
        }
        throw new HttpNotFoundException();
    }
    public function resolve(Request $request): Response
    {
        $route = $this->resolveRoute($request);
        $request->setRoute($route);
        $action = $route->action();

        $middlewares = $route->middlewares();

        if(is_array($action)) {
            $controller = singleton($action[0], \DI\autowire($action[0]));
            $action[0] = $controller;
            $middlewares = array_merge($middlewares, $controller->middlewares());
        }

        $params = DependencyInjection::resolveParameters($action, $request->routeParameters());

        if ($route->hasMiddlewares()) {
            return $this->runMiddlewares(
                $request,
                $middlewares,
                fn () => call_user_func($action, ...$params)
            );
        }
        return $action($request);
    }
    protected function runMiddlewares(Request $request, array $middlewares, \Closure $target): Response
    {
        if (count($middlewares) === 0) {
            return $target();
        }

        return $middlewares[0]->handle(
            $request,
            fn ($request) => $this->runMiddlewares($request, array_slice($middlewares, 1), $target)
        );
    }
    protected function verifyIfExistsRouteWithDuplicatedName(Route $newRoute)
    {
        foreach($this->routes as $method) {
            foreach($method as $route) {
                if($route->name() === $newRoute->name() && $newRoute->name() !== null) {
                    throw new RouteDuplicatedNameException($newRoute->name() ?? '');
                }
            }
        }
    }
    /**
     * Register a GET route with the give `$uri` and `$action`
     *
     * @param string $uri
     * @param \Closure $action
     * @return Route
     */
    public function get(string $uri, \Closure|array $action): Route
    {
        return $this->registerRoute(HttpMethod::GET, $uri, $action);
    }

    /**
     * Register a POST route with the given `$uri` and `$action`
     *
     * @param string $uri
     * @param \Closure $action
     * @return Route
     */
    public function post(string $uri, \Closure|array $action): Route
    {
        return $this->registerRoute(HttpMethod::POST, $uri, $action);
    }

    /**
     * Register a PUT route with the given `$uri` and `$action`
     *
     * @param string $uri
     * @param \Closure $action
     * @return Route
     */
    public function put(string $uri, \Closure|array $action): Route
    {
        return $this->registerRoute(HttpMethod::PUT, $uri, $action);
    }

    /**
     * Register a DELETE route with the given `$uri` and `$action`
     *
     * @param string $uri
     * @param \Closure $action
     * @return Route
     */
    public function delete(string $uri, \Closure|array $action): Route
    {
        return $this->registerRoute(HttpMethod::DELETE, $uri, $action);
    }

    /**
     * Register a PATCH route with the given `$uri` and `$action`
     *
     * @param string $uri
     * @param \Closure $action
     * @return Route
     */
    public function patch(string $uri, \Closure|array $action): Route
    {
        return $this->registerRoute(HttpMethod::PATCH, $uri, $action);
    }
}
