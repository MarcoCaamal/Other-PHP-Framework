<?php
namespace Junk\Tests;

use Junk\HttpMethod;
use Junk\ServerContract;

class MockServer implements ServerContract {


    public function __construct(public string $uri, public HttpMethod $method) {
        $this->uri = $uri;
        $this->method = $method;
    }
    
    /**
     * @return array
     */
    public function postData(): array {
        return [];
    }
    
    /**
     * @return array
     */
    public function queryParams(): array {
        return [];
    }
    
    /**
     * @return HttpMethod
     */
    public function requestMethod(): HttpMethod {
        return $this->method;
    }
    
    /**
     * @return string
     */
    public function requestUri(): string {
        return $this->uri;
    }
}