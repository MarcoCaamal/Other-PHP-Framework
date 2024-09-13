<?php

namespace OtherPHPFramework\Http;

use OtherPHPFramework\Http\HttpMethod;
use OtherPHPFramework\Routing\Route;
use OtherPHPFramework\Validation\Validator;

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
     * HTTP Headers
     *
     * @var array
     */
    protected array $headers = [];
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
     * Get POST data as key-value or get only specific value providing a `$key`
     *
     * @return array|string|int|null Null if the key dosen't exists,
     * the value of the key if it present or all data if no key was provided.
     */
    public function data(?string $key = null): array|string|int|null
    {
        if (is_null($key)) {
            return $this->data;
        }

        return $this->data[$key] ?? null;
    }
    /**
     * Get Query parameters as key-value or only specific value providing a `$key`
     *
     * @return array|string|int|null Null if the key dosen't exists,
     * the value of the key if it present or all data if no key was provided.
     */
    public function query(?string $key = null): array|string|int|null
    {
        if (is_null($key)) {
            return $this->query;
        }

        return $this->query[$key] ?? null;
    }
    /**
     * Get request headers as key-value or only specific value providing a `$key`
     *
     * @return array|string|null Null if the key dosen't exists,
     * the value of the key if it present or all data if no key was provided.
     */
    public function headers(?string $key = null): array|string|null
    {
        if (is_null($key)) {
            return $this->headers;
        }

        return $this->headers[strtolower($key)] ?? null;
    }
    /**
     * Set request headers fpr this request
     *
     * @param array $headers
     * @return self
     */
    public function setHeaders(array $headers): self
    {
        foreach ($headers as $header => $value) {
            $this->headers[strtolower($header)] = $value;
        }

        return $this;
    }
    public function setHeader(string $key, string $value): self
    {
        $this->headers[strtolower($key)] = $value;
        return $this;
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
     * Get all route parameters or only specific value by providing `$key`
     *
     * @return array|string|int|null Null if the key dosen't exists,
     * the value of the key if it present or all data if no key was provided.
     */
    public function routeParameters(?string $key = null): array|string|int|null
    {
        $parameters = $this->route->parseParameters($this->uri);

        if (is_null($key)) {
            return $parameters;
        }

        return $parameters[$key] ?? null;
    }
    public function validate(array $rules, array $messages = []): array
    {
        $validator = new Validator($this->data);

        return $validator->validate($rules, $messages);
    }
}
