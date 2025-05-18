<?php

use LightWeight\Http\Contracts\RequestContract;
use LightWeight\Http\Contracts\ResponseContract;
use LightWeight\Http\Request;
use LightWeight\Http\Response;

function json(mixed $data, int $statusCode = 200): ResponseContract
{
    return Response::json($data)->setStatus($statusCode);
}
function redirect(string $uri): ResponseContract
{
    return Response::redirect($uri);
}
function view(string $view, array $data = [], ?string $layout = null): ResponseContract
{
    return Response::view($view, $data, $layout);
}
function request(): RequestContract
{
    return app(\LightWeight\App::class)->request;
}
function response(): Response
{
    return app(\LightWeight\App::class)->make(Response::class);
}
function back(): ResponseContract
{
    return redirect(session()->get('_previous', '/'));
}
