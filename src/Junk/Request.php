<?php
namespace Junk;

use Junk\PHPNativeServer;
use Junk\Server;
use Junk\HttpMethod;

class Request {
    protected string $uri;
    protected HttpMethod $method;
    protected array $data = [];
    protected array $query = [];

    public function __construct(ServerContract $server) {
        $this->uri = $server->requestUri();
        $this->method = $server->requestMethod();
        $this->data = $server->postData();
        $this->query = $server->queryParams();
    }

    public function uri(): string {
        return $this->uri;
    }

    public function method(): HttpMethod {
        return $this->method;
    }
}