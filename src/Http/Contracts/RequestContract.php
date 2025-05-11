<?php

namespace LightWeight\Http\Contracts;

use LightWeight\Http\HttpMethod;
use LightWeight\Routing\Route;
use LightWeight\Storage\File;

/**
 * Contract for HTTP Request handling.
 */
interface RequestContract
{
    /**
     * Get the request URI.
     */
    public function uri(): string;
    
    /**
     * Get the request HTTP method.
     */
    public function method(): HttpMethod;
    
    /**
     * Get POST data as key-value or get only specific value providing a `$key`
     *
     * @return array|string|int|null Null if the key doesn't exist,
     * the value of the key if it's present or all data if no key was provided.
     */
    public function data(?string $key = null): array|string|int|null;
    
    /**
     * Get Query parameters as key-value or only specific value providing a `$key`
     *
     * @return array|string|int|null Null if the key doesn't exist,
     * the value of the key if it's present or all data if no key was provided.
     */
    public function query(?string $key = null): array|string|int|null;
    
    /**
     * Get request headers as key-value or only specific value providing a `$key`
     *
     * @return array|string|null Null if the key doesn't exist,
     * the value of the key if it's present or all data if no key was provided.
     */
    public function headers(?string $key = null): array|string|null;
    
    /**
     * Set request headers for this request
     *
     * @param array $headers
     * @return self
     */
    public function setHeaders(array $headers): self;
    
    /**
     * Set a single request header
     *
     * @param string $key
     * @param string $value
     * @return self
     */
    public function setHeader(string $key, string $value): self;
    
    /**
     * Get file from request.
     *
     * @param string $name
     * @return File|null
     */
    public function file(string $name): ?File;
    
    /**
     * Set uploaded files.
     *
     * @param array<string, \LightWeight\Storage\File> $files
     * @return self
     */
    public function setFiles(array $files): self;
    
    /**
     * URI requested by the client.
     *
     * @param string $uri URI requested by the client.
     * @return self
     */
    public function setUri(string $uri): self;
    
    /**
     * Get route matched by URI of this request
     *
     * @return Route
     */
    public function getRoute(): Route;
    
    /**
     * Set route matched by URI of this request
     *
     * @param Route $route Route matched by URI
     * @return self
     */
    public function setRoute(Route $route): self;
    
    /**
     * Set the HTTP method used for this request.
     *
     * @param HttpMethod $method HTTP method used for this request.
     * @return self
     */
    public function setMethod(HttpMethod $method): self;
    
    /**
     * Set the query parameters for this request.
     *
     * @param array $query Query parameters.
     * @return self
     */
    public function setQueryParameters(array $query): self;
    
    /**
     * Set POST data.
     *
     * @param array $data POST data.
     * @return self
     */
    public function setPostData(array $data): self;
    
    /**
     * Get all route parameters or only specific value by providing `$key`
     *
     * @return array|string|int|null Null if the key doesn't exist,
     * the value of the key if it's present or all data if no key was provided.
     */
    public function routeParameters(?string $key = null): array|string|int|null;
    
    /**
     * Validate request data against rules
     *
     * @param array $rules Validation rules
     * @param array $messages Custom error messages
     * @return array Validated data
     */
    public function validate(array $rules, array $messages = []): array;
}
