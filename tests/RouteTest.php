<?php
namespace Junk\Tests;

use Junk\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase {
    public static function routesWithoutParametersProvider() {
        return [
            ['/'],
            ['/test'],
            ['/random-route'],
            ['/something'],
            ['/route/something'],
            ['/test/other-test'],
            ['i-dont-know']
        ];
    }

    #[DataProvider('routesWithoutParametersProvider')]
    public function testRegexWithoutParameters(string $uri) {
        $route = new Route($uri, fn() => 'test');

        $this->assertTrue($route->matches($uri));
        $this->assertFalse($route->matches('/extra/route'));
        $this->assertFalse($route->matches('/random/path/route'));
        $this->assertFalse($route->matches("/some/$uri"));
    }

    #[DataProvider("routesWithoutParametersProvider")]
    public function testRegexOnUriThatEndsWithSlash(string $uri) { 
        $route = new Route($uri, fn() => "test");
        $this->assertTrue($route->matches("$uri/"));
    }
}