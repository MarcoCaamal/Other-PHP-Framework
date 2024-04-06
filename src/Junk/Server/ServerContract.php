<?php
namespace Junk\Server;

use Junk\Http\HttpMethod;

interface ServerContract {
    public function requestUri(): string;

    public function requestMethod(): HttpMethod;

    public function postData(): array;

    public function queryParams(): array;
}