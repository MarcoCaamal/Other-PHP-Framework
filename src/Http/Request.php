<?php

namespace Junk\Http;

use Junk\Server\ServerContract;
use Junk\Http\HttpMethod;
use Junk\Routing\Route;

/**
 * This class represent a Request HTTP.
 *
 */
class Request
{
    /**
     * URI requested by the client.
     *
     * @var string
     */
    protected string $uri;
    /**
     * Route matched by URI
     *
     * @var Route
     */
    protected Route $route;
    /**
     * HTTP method used for this request.
     *
     * @var HttpMethod
     */
    protected HttpMethod $method;
    /**
     * POST data.
     *
     * @var array
     */
    protected array $data = [];
    /**
     * Query parameters.
     *
     * @var array
     */
    protected array $query = [];
    /**
     * Create a new **Request** from the given `$server`.
     */
    public function __construct()
    {
    }
    /**
     * Get the request URI.
     *
     */
    public function uri(): string
    {
        return $this->uri;
    }
    /**
     * Get the request HTTP method.
     *
     */
    public function method(): HttpMethod
    {
        return $this->method;
    }
    /**
     * Get POST data
     *
     * @return array
     */
    public function data(): array
    {
        return $this->data;
    }
    /**
     * Get Query parameters
     *
     * @return array
     */
    public function query(): array
    {
        return $this->query;
    }
    /**
     * URI requested by the client.
     *
     * @param string $uri URI requested by the client.
     * @return self
     */
    public function setUri(string $uri): self
    {
        $this->uri = $uri;
        return $this;
    }
    /**
     * Get route matched by URI of this request
     *
     * @return Route
     */
    public function getRoute(): Route
    {
        return $this->route;
    }
    /**
     * Set route matched by URI of this request
     *
     * @param Route $route Route matched by URI
     * @return self
     */
    public function setRoute(Route $route): self
    {
        $this->route = $route;
        return $this;
    }
    /**
     * Set the HTTP method used for this request.
     *
     * @param HttpMethod $method HTTP method used for this request.
     * @return self
     */
    public function setMethod(HttpMethod $method): self
    {
        $this->method = $method;
        return $this;
    }
    /**
     * Set the query parameters for this request.
     *
     * @param array $query Query parameters.
     * @return self
     */
    public function setQueryParameters(array $query): self
    {
        $this->query = $query;
        return $this;
    }
    /**
     * Set POST data.
     *
     * @param array $data POST data.
     * @return self
     */
    public function setPostData(array $data): self
    {
        $this->data = $data;
        return $this;
    }
    /**
     * Get all route parameters.
     *
     * @return array
     */
    public function routeParameters(): array
    {
        return $this->route->parseParameters($this->uri);
    }
}
