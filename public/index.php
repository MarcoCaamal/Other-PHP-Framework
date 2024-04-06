<?php

use Junk\Http\Response;
use Junk\Server\PHPNativeServer;

use Junk\Http\Request;
use Junk\Http\HttpNotFoundException;
use Junk\Routing\Router;

require __DIR__ . "/../vendor/autoload.php";

$router = new Router();

$router->get("/", function (Request $request) {
    return Response::text("Server is listen...");
});

$router->get('/test/{id}', function (Request $request) {
    return Response::json($request->routeParameters());
});

$router->post('/test', function (Request $request) {
    return Response::json($request->data());
});

$server = new PHPNativeServer();
try {
    $request = $server->getRequest();
    $route = $router->resolve($request);
    $request->setRoute($route);
    $action = $route->action();
    $response = $action($request);
    $server->sendResponse($response);
} catch (HttpNotFoundException $ex) {
    print($ex);
    http_response_code(404);
}
