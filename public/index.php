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
            return json(['message' => 'Not Authenticated']);
        }

        return $next();
    }
}

$app = App::bootstrap();

Route::get('/', fn () => json(['GET OK']))->setMiddlewares([AuthMiddleware::class]);

Route::get('/html', fn (Request $request) => view('home', [
    'user' => 'Guest'
]));

$app->run();
