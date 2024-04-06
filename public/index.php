<?php
use Junk\Http\Response;
use Junk\Server\PHPNativeServer;

use Junk\Http\Request;
use Junk\Http\HttpNotFoundException;
use Junk\Routing\Router;

require __DIR__ . "/../vendor/autoload.php";

$router = new Router();

$router->get("/", function (Request $request) { 
    return Response::text("GET OK");
});

$router->get('/test/{id}', function () { 
    return Response::json(['message' => 'TEST OK']);
});

$router->post('/test', function () { 
    return 'POST OK';
});

$server = new PHPNativeServer();
try {
    $request = new Request($server);
    $route = $router->resolve($request);
    $action = $route->action();
    $response = $action($request);
    $server->sendResponse($response);
} catch (HttpNotFoundException $ex) {
    print($ex);
    http_response_code(404);
}