<?php
namespace Junk\Server;

use Junk\Http\HttpMethod;
use Junk\Http\Response;

interface ServerContract {
    public function requestUri(): string;

    public function requestMethod(): HttpMethod;

    public function postData(): array;

    public function queryParams(): array;
    public function sendResponse(Response $response);
}