<?php

namespace SMFramework\Routing;

/**
 * This class represents one route that stores URI regex and action.
 *
 */
class Route
{
    /**
     * URI defined int the format `"/route/{param}"`.
     *
     * @var string
     */
    protected string $uri;
    /**
     * Action associated to this URI
     *
     * @var \Closure
     */
    protected \Closure|array $action;
    /**
     * Regular expresion used to match incoming requests URIs.
     *
     * @var string
     */
    protected string $regex;
    /**
     * Route parameters names.
     * @var array<string>
     */
    protected array $parameters = [];
    /**
     * HTTP Middlewares
     *
     * @var array<\SMFramework\Http\Contracts\MiddlewareContract>
     */
    protected array $middlewares = [];
    /**
     * Create a new route with the given URI and action
     *
     * @param string $uri
     * @param \Closure $action
     */
    public function __construct(string $uri, \Closure|array $action)
    {
        $this->uri = $uri;
        $this->action = $action;
        $this->regex = preg_replace('/\{([a-zA-Z]+)\}/', '([a-zA-Z0-9]+)', $uri);
        preg_match_all('/\{([a-zA-Z0-9]+)\}/', $uri, $parameters);
        $this->parameters = $parameters[1];
    }
    /**
     * Get a URI definition for this route
     *
     * @return string
     */
    public function uri(): string
    {
        return $this->uri;
    }
    /**
     * Get the action that handles requests to the this URI.
     *
     * @return \Closure
     */
    public function action(): \Closure|array
    {
        return $this->action;
    }
    /**
     * Get HTTP Middlewares for this route
     * @return array<\SMFramework\Http\Contracts\MiddlewareContract>
     */
    public function middlewares(): array
    {
        return $this->middlewares;
    }
    public function setMiddlewares(array $middlewares): self
    {
        $this->middlewares = array_map(fn ($middleware) => new $middleware(), $middlewares);
        return $this;
    }
    public function hasMiddlewares(): bool
    {
        return count($this->middlewares) > 0;
    }
    /**
     * Check if the given `$uri` matches the regex of this route.
     *
     * @param string $uri
     * @return bool
     */
    public function matches(string $uri): bool
    {
        return preg_match("#^$this->regex/?$#", $uri);
    }
    /**
     * Check if this route has variable parameters in its definition
     *
     * @return bool
     */
    public function hasParameters(): bool
    {
        return count($this->parameters) > 0;
    }
    /**
     * Get the key-value pairs from the `$uri` as defined by this route
     *
     */
    public function parseParameters(string $uri): array
    {
        preg_match("#^$this->regex$#", $uri, $arguments);

        return array_combine($this->parameters, array_slice($arguments, 1));
    }
    public static function load(string $routesDirectory)
    {
        foreach(glob("$routesDirectory/*.php") as $routes) {
            require_once $routes;
        }
    }
    public static function get(string $uri, \Closure|array $action): self
    {
        return app()->router->get($uri, $action);
    }

    public static function post(string $uri, \Closure|array $action): self
    {
        return app()->router->post($uri, $action);
    }
    public static function put(string $uri, \Closure|array $action): Route
    {
        return app()->router->put($uri, $action);
    }
    public static function delete(string $uri, \Closure|array $action): Route
    {
        return app()->router->delete($uri, $action);
    }
}
