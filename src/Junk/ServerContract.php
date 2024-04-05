<?php
namespace Junk;

interface ServerContract {
    public function requestUri(): string;

    public function requestMethod(): HttpMethod;

    public function postData(): array;

    public function queryParams(): array;
}