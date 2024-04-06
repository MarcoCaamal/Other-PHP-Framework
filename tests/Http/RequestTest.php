<?php

namespace Junk\Tests\Http;

use Junk\Http\HttpMethod;
use Junk\Http\Request;
use Junk\Server\ServerContract;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function testRequestReturnsDataObtainedFromServerCorrectly()
    {
        $uri = '/test/route';
        $queryParams = ['a' => 'Query Param Test A', 'b' => 'Query Param Test B', 'c' => 'Query Param Test C', 'foo' => 1];
        $postData = ['data' => 'test', 'randomData' => 2];

        $request = (new Request())
            ->setUri($uri)
            ->setMethod(HttpMethod::POST)
            ->setQueryParameters($queryParams)
            ->setPostData($postData);

        $this->assertEquals($uri, $request->uri());
        $this->assertEquals($queryParams, $request->query());
        $this->assertEquals($postData, $request->data());
        $this->assertEquals(HttpMethod::POST, $request->method());
    }
}
