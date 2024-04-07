<?php

namespace Junk\Server;

use Junk\Http\HttpMethod;
use Junk\Http\Request;
use Junk\Server\Contracts\ServerContract;

/**
 * PHP native server that uses `$_SERVER` global.
 */
class PHPNativeServer implements ServerContract
{
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
        foreach ($response->headers() as $header => $value) {
            header("$header: $value");
        }
        print($response->getContent());
    }
    /**
     * @inheritDoc
     */
    public function getRequest(): Request
    {
        return (new Request())
            ->setUri(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH))
            ->setMethod(HttpMethod::from($_SERVER['REQUEST_METHOD']))
            ->setHeaders(getallheaders())
            ->setPostData($_POST)
            ->setQueryParameters($_GET);
    }
}
