<?php

use LightWeight\Http\Request;
use LightWeight\Http\Response;

function json(array $data): Response
{
    return Response::json($data);
}
function redirect(string $uri): Response
{
    return Response::redirect($uri);
}
function view(string $view, array $data = [], ?string $layout = null): Response
{
    return Response::view($view, $data, $layout);
}
function request(): Request
{
    return app(\LightWeight\App::class)->request;
}
function back(): Response
{
    return redirect(session()->get('_previous', '/'));
}
