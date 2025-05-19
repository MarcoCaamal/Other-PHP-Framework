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
    
    /**
     * Get an attribute value or default.
     *
     * @param string $key The attribute key
     * @param mixed $default Default value to return if attribute doesn't exist
     * @return mixed The attribute value or default
     */
    public function getAttribute(string $key, mixed $default = null): mixed;
    
    /**
     * Add an attribute to the request.
     *
     * @param string $key The attribute key
     * @param mixed $value The value to store
     * @return self
     */
    public function addAttribute(string $key, mixed $value): self;
    
    /**
     * Check if an attribute exists.
     *
     * @param string $key The attribute key
     * @return bool True if the attribute exists
     */
    public function hasAttribute(string $key): bool;
    
    /**
     * Get all attributes.
     *
     * @return array All request attributes
     */
    public function getAttributes(): array;
    
    /**
     * Get the full URL of the request
     *
     * @return string The complete URL
     */
    public function url(): string;
    
    /**
     * Get the URL without query string
     *
     * @return string The URL without query parameters
     */
    public function baseUrl(): string;
    
    /**
     * Get the request path (URI without query string)
     *
     * @return string The path component of the URL
     */
    public function path(): string;
    
    /**
     * Get the URL scheme (http or https)
     *
     * @return string The URL scheme
     */
    public function scheme(): string;
    
    /**
     * Check if the request is using HTTPS
     *
     * @return bool True if using HTTPS
     */
    public function isSecure(): bool;
    
    /**
     * Get the host name
     *
     * @return string The host name
     */
    public function host(): string;
    
    /**
     * Get the port number
     *
     * @return int|null The port number or null if using standard ports
     */
    public function port(): ?int;
    
    /**
     * Get the client's IP address
     *
     * @return string|null The IP address
     */
    public function ip(): ?string;
    
    /**
     * Get the user agent string
     *
     * @return string|null The user agent
     */
    public function userAgent(): ?string;
    
    /**
     * Check if the request is an AJAX request
     *
     * @return bool True if AJAX request
     */
    public function isAjax(): bool;
    
    /**
     * Check if the request is expecting a JSON response
     *
     * @return bool True if expecting JSON
     */
    public function expectsJson(): bool;
    
    /**
     * Get the raw request body content
     *
     * @return string|null The raw content
     */
    public function getContent(): ?string;
    
    /**
     * Get the request content type
     *
     * @return string|null The content type
     */
    public function contentType(): ?string;
    
    /**
     * Check if the request is a specific content type
     *
     * @param string $type The content type to check
     * @return bool True if matches
     */
    public function isContentType(string $type): bool;
    
    /**
     * Get the referer URL
     *
     * @return string|null The referer
     */
    public function referer(): ?string;
    
    /**
     * Set the scheme for this request
     *
     * @param string $scheme The URL scheme
     * @return self
     */
    public function setScheme(string $scheme): self;
    
    /**
     * Set the host for this request
     *
     * @param string $host The host name
     * @return self
     */
    public function setHost(string $host): self;
    
    /**
     * Set the port for this request
     *
     * @param int|null $port The port number
     * @return self
     */
    public function setPort(?int $port): self;
    
    /**
     * Set the client IP address
     *
     * @param string|null $ip The IP address
     * @return self
     */
    public function setIp(?string $ip): self;
    
    /**
     * Set the user agent
     *
     * @param string|null $userAgent The user agent string
     * @return self
     */
    public function setUserAgent(?string $userAgent): self;
    
    /**
     * Set the referer
     *
     * @param string|null $referer The referer URL
     * @return self
     */
    public function setReferer(?string $referer): self;
    
    /**
     * Set whether this is an AJAX request
     *
     * @param bool $ajax Whether this is an AJAX request
     * @return self
     */
    public function setAjax(bool $ajax): self;
    
    /**
     * Set the raw request content
     *
     * @param string|null $content The raw content
     * @return self
     */
    public function setContent(?string $content): self;
    
    /**
     * Set the content type
     *
     * @param string|null $contentType The content type
     * @return self
     */
    public function setContentType(?string $contentType): self;
    
    /**
     * Check if the request has a specific input value
     *
     * @param string $key The input key to check
     * @return bool True if the input exists
     */
    public function has(string $key): bool;
    
    /**
     * Get a value from the combined input (POST data + query parameters)
     *
     * @param string|null $key The input key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed The input value or default
     */
    public function input(?string $key = null, mixed $default = null): mixed;
    
    /**
     * Get all inputs except specified ones
     *
     * @param array $keys Keys to exclude
     * @return array Filtered input data
     */
    public function except(array $keys): array;
    
    /**
     * Get only specified inputs
     *
     * @param array $keys Keys to include
     * @return array Filtered input data
     */
    public function only(array $keys): array;
    
    /**
     * Check if the request is for a specific method
     *
     * @param HttpMethod $method HTTP method to check
     * @return bool True if matches
     */
    public function isMethod(HttpMethod $method): bool;
    
    /**
     * Check if the current request is a GET request
     *
     * @return bool True if GET request
     */
    public function isGet(): bool;
    
    /**
     * Check if the current request is a POST request
     *
     * @return bool True if POST request
     */
    public function isPost(): bool;
    
    /**
     * Check if the current request is a PUT request
     *
     * @return bool True if PUT request
     */
    public function isPut(): bool;
    
    /**
     * Check if the current request is a DELETE request
     *
     * @return bool True if DELETE request
     */
    public function isDelete(): bool;
    
    /**
     * Check if the current request is a PATCH request
     *
     * @return bool True if PATCH request
     */
    public function isPatch(): bool;
    
    /**
     * Check if the current request is an OPTIONS request
     *
     * @return bool True if OPTIONS request
     */
    public function isOptions(): bool;
}
