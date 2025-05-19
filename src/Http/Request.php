<?php

namespace LightWeight\Http;

use LightWeight\Http\Contracts\RequestContract;
use LightWeight\Http\HttpMethod;
use LightWeight\Routing\Route;
use LightWeight\Storage\File;
use LightWeight\Validation\Validator;

/**
 * This class represent a Request HTTP.
 *
 */
class Request implements RequestContract
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
     * Uploaded files.
     *
     * @var array<string, File>
     */
    protected array $files = [];
    
    /**
     * Request attributes.
     *
     * @var array
     */
    protected array $attributes = [];

    /**
     * Host/domain information
     *
     * @var string
     */
    protected string $host = '';
    
    /**
     * URL scheme (http/https)
     *
     * @var string
     */
    protected string $scheme = 'http';
    
    /**
     * Port number
     *
     * @var int|null
     */
    protected ?int $port = null;
    
    /**
     * User agent information
     *
     * @var string|null
     */
    protected ?string $userAgent = null;
    
    /**
     * IP address of the client
     *
     * @var string|null
     */
    protected ?string $ip = null;
    
    /**
     * Referer URL
     *
     * @var string|null
     */
    protected ?string $referer = null;
    
    /**
     * Request content type
     *
     * @var string|null
     */
    protected ?string $contentType = null;
    
    /**
     * Whether request is an AJAX request
     *
     * @var bool
     */
    protected bool $ajax = false;
    
    /**
     * Raw request body content
     *
     * @var string|null
     */
    protected ?string $content = null;

    /**
     * Create a new **Request** from the given `$server`.
     */
    public function __construct()
    {
        // Esta implementación se mantendrá vacía para permitir
        // la inicialización de la Request desde Factory methods
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
     * Get file from request.
     *
     * @param string $name
     * @return File|null
     */
    public function file(string $name): ?File
    {
        return $this->files[$name] ?? null;
    }
    /**
     * Set uploaded files.
     *
     * @param array<string, \LightWeight\Storage\File> $files
     * @return self
     */
    public function setFiles(array $files): self
    {
        $this->files = $files;
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
    /**
     * Validate request data against rules
     *
     * @param array $rules Validation rules
     * @param array $messages Custom error messages
     * @return array Validated data
     */
    public function validate(array $rules, array $messages = []): array
    {
        $validator = new Validator($this->data);
        return $validator->validate($rules, $messages);
    }
    
    /**
     * Get an attribute value or default.
     *
     * @param string $key The attribute key
     * @param mixed $default Default value to return if attribute doesn't exist
     * @return mixed The attribute value or default
     */
    public function getAttribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }
    
    /**
     * Add an attribute to the request.
     *
     * @param string $key The attribute key
     * @param mixed $value The value to store
     * @return self
     */
    public function addAttribute(string $key, mixed $value): self
    {
        $this->attributes[$key] = $value;
        return $this;
    }
    
    /**
     * Check if an attribute exists.
     *
     * @param string $key The attribute key
     * @return bool True if the attribute exists
     */
    public function hasAttribute(string $key): bool
    {
        return isset($this->attributes[$key]);
    }
    
    /**
     * Get all attributes.
     *
     * @return array All request attributes
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Get the full URL of the request
     *
     * @return string The complete URL
     */
    public function url(): string
    {
        $url = $this->scheme . '://' . $this->host;
        
        // Add port if not standard
        if ($this->port && !(($this->scheme === 'http' && $this->port === 80) || 
                           ($this->scheme === 'https' && $this->port === 443))) {
            $url .= ':' . $this->port;
        }
        
        // Add URI
        $url .= '/' . ltrim($this->uri, '/');
        
        // Add query string if exists
        if (!empty($this->query)) {
            $url .= '?' . http_build_query($this->query);
        }
        
        return $url;
    }
    
    /**
     * Get the URL without query string
     *
     * @return string The URL without query parameters
     */
    public function baseUrl(): string
    {
        $url = $this->scheme . '://' . $this->host;
        
        // Add port if not standard
        if ($this->port && !(($this->scheme === 'http' && $this->port === 80) || 
                           ($this->scheme === 'https' && $this->port === 443))) {
            $url .= ':' . $this->port;
        }
        
        return $url;
    }
    
    /**
     * Get the request path (URI without query string)
     *
     * @return string The path component of the URL
     */
    public function path(): string
    {
        return '/' . ltrim($this->uri, '/');
    }
    
    /**
     * Get the URL scheme (http or https)
     *
     * @return string The URL scheme
     */
    public function scheme(): string
    {
        return $this->scheme;
    }
    
    /**
     * Check if the request is using HTTPS
     *
     * @return bool True if using HTTPS
     */
    public function isSecure(): bool
    {
        return $this->scheme === 'https';
    }
    
    /**
     * Get the host name
     *
     * @return string The host name
     */
    public function host(): string
    {
        return $this->host;
    }
    
    /**
     * Get the port number
     *
     * @return int|null The port number or null if using standard ports
     */
    public function port(): ?int
    {
        return $this->port;
    }
    
    /**
     * Get the client's IP address
     *
     * @return string|null The IP address
     */
    public function ip(): ?string
    {
        return $this->ip;
    }
    
    /**
     * Get the user agent string
     *
     * @return string|null The user agent
     */
    public function userAgent(): ?string
    {
        return $this->userAgent;
    }
    
    /**
     * Check if the request is an AJAX request
     *
     * @return bool True if AJAX request
     */
    public function isAjax(): bool
    {
        return $this->ajax;
    }
    
    /**
     * Check if the request is expecting a JSON response
     *
     * @return bool True if expecting JSON
     */
    public function expectsJson(): bool
    {
        return $this->headers('accept') && 
               strpos($this->headers('accept'), 'application/json') !== false;
    }
    
    /**
     * Get the raw request body content
     *
     * @return string|null The raw content
     */
    public function getContent(): ?string
    {
        return $this->content;
    }
    
    /**
     * Get the request content type
     *
     * @return string|null The content type
     */
    public function contentType(): ?string
    {
        return $this->contentType;
    }
    
    /**
     * Check if the request is a specific content type
     *
     * @param string $type The content type to check
     * @return bool True if matches
     */
    public function isContentType(string $type): bool
    {
        return $this->contentType && strpos($this->contentType, $type) !== false;
    }
    
    /**
     * Get the referer URL
     *
     * @return string|null The referer
     */
    public function referer(): ?string
    {
        return $this->referer;
    }
    
    /**
     * Set the scheme for this request
     *
     * @param string $scheme The URL scheme
     * @return self
     */
    public function setScheme(string $scheme): self
    {
        $this->scheme = $scheme;
        return $this;
    }
    
    /**
     * Set the host for this request
     *
     * @param string $host The host name
     * @return self
     */
    public function setHost(string $host): self
    {
        $this->host = $host;
        return $this;
    }
    
    /**
     * Set the port for this request
     *
     * @param int|null $port The port number
     * @return self
     */
    public function setPort(?int $port): self
    {
        $this->port = $port;
        return $this;
    }
    
    /**
     * Set the client IP address
     *
     * @param string|null $ip The IP address
     * @return self
     */
    public function setIp(?string $ip): self
    {
        $this->ip = $ip;
        return $this;
    }
    
    /**
     * Set the user agent
     *
     * @param string|null $userAgent The user agent string
     * @return self
     */
    public function setUserAgent(?string $userAgent): self
    {
        $this->userAgent = $userAgent;
        return $this;
    }
    
    /**
     * Set the referer
     *
     * @param string|null $referer The referer URL
     * @return self
     */
    public function setReferer(?string $referer): self
    {
        $this->referer = $referer;
        return $this;
    }
    
    /**
     * Set whether this is an AJAX request
     *
     * @param bool $ajax Whether this is an AJAX request
     * @return self
     */
    public function setAjax(bool $ajax): self
    {
        $this->ajax = $ajax;
        return $this;
    }
    
    /**
     * Set the raw request content
     *
     * @param string|null $content The raw content
     * @return self
     */
    public function setContent(?string $content): self
    {
        $this->content = $content;
        return $this;
    }
    
    /**
     * Set the content type
     *
     * @param string|null $contentType The content type
     * @return self
     */
    public function setContentType(?string $contentType): self
    {
        $this->contentType = $contentType;
        return $this;
    }
    
    /**
     * Check if the request has a specific input value
     *
     * @param string $key The input key to check
     * @return bool True if the input exists
     */
    public function has(string $key): bool
    {
        return isset($this->data[$key]) || isset($this->query[$key]);
    }
    
    /**
     * Get a value from the combined input (POST data + query parameters)
     *
     * @param string|null $key The input key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed The input value or default
     */
    public function input(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return array_merge($this->query, $this->data);
        }
        
        // Check in POST data first, then query parameters
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }
        
        if (isset($this->query[$key])) {
            return $this->query[$key];
        }
        
        return $default;
    }
    
    /**
     * Get all inputs except specified ones
     *
     * @param array $keys Keys to exclude
     * @return array Filtered input data
     */
    public function except(array $keys): array
    {
        $inputData = $this->input();
        
        foreach ($keys as $key) {
            unset($inputData[$key]);
        }
        
        return $inputData;
    }
    
    /**
     * Get only specified inputs
     *
     * @param array $keys Keys to include
     * @return array Filtered input data
     */
    public function only(array $keys): array
    {
        $results = [];
        $inputData = $this->input();
        
        foreach ($keys as $key) {
            if (isset($inputData[$key])) {
                $results[$key] = $inputData[$key];
            }
        }
        
        return $results;
    }
    
    /**
     * Check if the request is for a specific method
     *
     * @param HttpMethod $method HTTP method to check
     * @return bool True if matches
     */
    public function isMethod(HttpMethod $method): bool
    {
        return $this->method === $method;
    }
    
    /**
     * Check if the current request is a GET request
     *
     * @return bool True if GET request
     */
    public function isGet(): bool
    {
        return $this->isMethod(HttpMethod::GET);
    }
    
    /**
     * Check if the current request is a POST request
     *
     * @return bool True if POST request
     */
    public function isPost(): bool
    {
        return $this->isMethod(HttpMethod::POST);
    }
    
    /**
     * Check if the current request is a PUT request
     *
     * @return bool True if PUT request
     */
    public function isPut(): bool
    {
        return $this->isMethod(HttpMethod::PUT);
    }
    
    /**
     * Check if the current request is a DELETE request
     *
     * @return bool True if DELETE request
     */
    public function isDelete(): bool
    {
        return $this->isMethod(HttpMethod::DELETE);
    }
    
    /**
     * Check if the current request is a PATCH request
     *
     * @return bool True if PATCH request
     */
    public function isPatch(): bool
    {
        return $this->isMethod(HttpMethod::PATCH);
    }
    
    /**
     * Check if the current request is an OPTIONS request
     *
     * @return bool True if OPTIONS request
     */
    public function isOptions(): bool
    {
        return $this->isMethod(HttpMethod::OPTIONS);
    }
}
