<?php
namespace Junk\Server;

use Junk\Server\ServerContract;
use Junk\Http\HttpMethod;

class PHPNativeServer implements ServerContract {
    
    /**
     * @return array
     */
    public function postData(): array {
        return $_POST;
    }
    
    /**
     * @return array
     */
    public function queryParams(): array {
        return $_GET;
    }
    
    /**
     * @return HttpMethod
     */
    public function requestMethod(): HttpMethod {
        return HttpMethod::from($_SERVER['REQUEST_METHOD']);
    }
    
    /**
     * @return string
     */
    public function requestUri(): string {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }
    /**
     *
     * @param \Junk\Http\Response $response
     */
    public function sendResponse(\Junk\Http\Response $response) {
        header('Content-Type: None');
        header_remove('Content-Type');

        $response->prepare();
        http_response_code($response->getStatus());
        foreach($response->getHeaders() as $header => $value) {
            header("$header: $value");
        }
        print($response->getContent());
    }
}