<?php

namespace Junk\Server;

use Junk\Server\ServerContract;
use Junk\Http\HttpMethod;

/**
 * PHP native server that uses `$_SERVER` global.
 */
class PHPNativeServer implements ServerContract
{
    /**
     * @inheritDoc
     */
    public function postData(): array
    {
        return $_POST;
    }

    /**
     * @inheritDoc
     */
    public function queryParams(): array
    {
        return $_GET;
    }

    /**
     * @inheritDoc
     */
    public function requestMethod(): HttpMethod
    {
        return HttpMethod::from($_SERVER['REQUEST_METHOD']);
    }

    /**
     * @inheritDoc
     */
    public function requestUri(): string
    {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }
    /**
     * @inheritDoc
     */
    public function sendResponse(\Junk\Http\Response $response)
    {
        // PHP sends Content-Type header by default, but it has to be removed if
        // the response has not content. Content-Type header can't be removed
        // unless it is set to some value before.
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
