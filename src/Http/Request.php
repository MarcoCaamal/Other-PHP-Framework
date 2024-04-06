<?php

namespace Junk\Http;

use Junk\Server\ServerContract;
use Junk\Http\HttpMethod;

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
    public function __construct(ServerContract $server)
    {
        $this->uri = $server->requestUri();
        $this->method = $server->requestMethod();
        $this->data = $server->postData();
        $this->query = $server->queryParams();
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
}
