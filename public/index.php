<?php

use Junk\App;
use Junk\Http\Request;
use Junk\Http\Response;

require __DIR__ . "/../vendor/autoload.php";

$app = App::bootstrap();

$app->router->get("/", function (Request $request) {
    return Response::text("Server is listen...");
});

$app->router->get('/test/{id}', function (Request $request) {
    return Response::json($request->routeParameters());
});

$app->router->post('/test', function (Request $request) {
    return Response::json($request->data());
});

$app->run();
