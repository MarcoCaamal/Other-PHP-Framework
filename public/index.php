<?php

use Junk\App;
use Junk\Http\Contracts\MiddlewareContract;
use Junk\Http\Request;
use Junk\Http\Response;
use Junk\Routing\Route;
use Junk\Validation\Rule;
use Junk\Validation\Rules\Required;

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

Route::post('/validate', fn (Request $request) => json($request->validate([
    'test' => Rule::required(),
    'num' => Rule::number(),
    'email' => [Rule::required(), Rule::email()]
], [
    'email' => [
        Required::class => 'DAME EL CAMPO'
    ]
])));

Route::get('/middlewares', fn () => json(['GET OK']))->setMiddlewares([AuthMiddleware::class]);

Route::get('/html', fn (Request $request) => view('home', [
    'user' => 'Guest'
]));

Route::get('/', function () {
    return Response::text('Hello World');
});

$app->run();
