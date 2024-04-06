<?php
use Junk\Http\Response;
use Junk\Server\PHPNativeServer;

use Junk\Http\Request;
use Junk\Http\HttpNotFoundException;
use Junk\Routing\Router;

require __DIR__ . "/../vendor/autoload.php";

$router = new Router();

$router->get("/", function (Request $request) { 
    $response = new Response();
    $response->setHeaders([
        'Content-Type' => 'application/json',
    ]);
    $response->setContent(json_encode(['message' => 'GET OK']));
    return $response;
});

$router->get('/test/{id}', function () { 
    return 'GET OK ';
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