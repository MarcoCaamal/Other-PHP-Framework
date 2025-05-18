<?php

namespace LightWeight\Routing;

/**
 * This class represents one route that stores URI regex and action.
 *
 */
class Route
{
    public static string $prefix = '';
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
     * Name of this route
     * @var
     */
    protected ?string $name = null;
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
     * @var array<\LightWeight\Http\Contracts\MiddlewareContract>
     */
    protected array $middlewares = [];
    /**
     * HTTP Middleware groups
     *
     * @var array<string>
     */
    protected array $middlewareGroups = [];

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
     * Get the name of the route
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->name;
    }
    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }
    /**
     * Get all HTTP middlewares for this route.
     *
     * @return \LightWeight\Http\Contracts\MiddlewareContract[]
     */
    public function middlewares(): array
    {
        return $this->middlewares;
    }
    /**
     * Set middlewares for this route.
     *
     * @param array<class-string<\LightWeight\Http\Contracts\MiddlewareContract>|\LightWeight\Http\Contracts\MiddlewareContract> $middlewares
     * @return self
     */
    public function setMiddlewares(array $middlewares): self
    {
        $this->middlewares = array_map(function ($middleware) {
            return is_string($middleware) ? new $middleware() : $middleware;
        }, $middlewares);
        return $this;
    }
    
    /**
     * Get all middleware groups for this route.
     * 
     * @return array<string>
     */
    public function middlewareGroups(): array
    {
        return $this->middlewareGroups;
    }
    
    /**
     * Set middleware groups for this route.
     * 
     * @param array<string> $groups
     * @return self
     */
    public function setMiddlewareGroups(array $groups): self
    {
        $this->middlewareGroups = $groups;
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
    public static function prefix(string $prefix, \Closure $callback)
    {
        self::$prefix = $prefix;
        $callback();
        self::$prefix = '';
    }
    public static function get(string $uri, \Closure|array $action): self
    {
        return app(\LightWeight\Routing\Router::class)->get($uri, $action);
    }

    public static function post(string $uri, \Closure|array $action): self
    {
        return app(\LightWeight\Routing\Router::class)->post($uri, $action);
    }
    public static function put(string $uri, \Closure|array $action): Route
    {
        return app(\LightWeight\Routing\Router::class)->put($uri, $action);
    }
    public static function delete(string $uri, \Closure|array $action): Route
    {
        return app(\LightWeight\Routing\Router::class)->delete($uri, $action);
    }
}
