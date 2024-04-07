<?php

use Junk\App;
use Junk\Http\Contracts\MiddlewareContract;
use Junk\Http\Request;
use Junk\Http\Response;
use Junk\Routing\Route;

require __DIR__ . "/../vendor/autoload.php";

class AuthMiddleware implements MiddlewareContract
{
    /**
     *
     * @param Request $request
     * @param Closure $next
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->headers('Authorization') === null) {
            return Response::json(['message' => 'Not Authenticated']);
        }

        return $next();
    }
}

$app = App::bootstrap();

Route::get('/', fn () => Response::json(['message' => 'GET OK']))->setMiddlewares([AuthMiddleware::class]);

$app->run();
