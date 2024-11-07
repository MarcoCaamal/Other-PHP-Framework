<?php

namespace LightWeight\Server;

use LightWeight\Http\HttpMethod;
use LightWeight\Http\Request;
use LightWeight\Server\Contracts\ServerContract;
use LightWeight\Storage\File;

/**
 * PHP native server that uses `$_SERVER` global.
 */
class PHPNativeServer implements ServerContract
{
    /**
     * Get files from `$_FILES` global.
     *
     * @return array<string, \LightWeight\Storage\File>
     */
    protected function uploadedFiles(): array
    {
        $files = [];
        foreach ($_FILES as $key => $file) {
            if (!empty($file["tmp_name"])) {
                $files[$key] = new File(
                    file_get_contents($file["tmp_name"]),
                    $file["type"],
                    $file["name"],
                );
            }
        }
        return $files;
    }
    /**
     * @inheritDoc
     */
    public function sendResponse(\LightWeight\Http\Response $response)
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
            ->setQueryParameters($_GET)
            ->setFiles($this->uploadedFiles());
    }
}
