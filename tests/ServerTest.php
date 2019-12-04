<?php

namespace Tests;

use Amber\Http\Server\RequestHandler;
use Amber\Http\Message\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use PHPUnit\Framework\TestCase;
use Tests\Example\NotFoundMiddleware;
use Tests\Example\JsonResponseMiddleware;

class ServerTest extends TestCase
{
    public function testRequestHandler()
    {
        $handler = new RequestHandler();

        $request = ServerRequest::fromGlobals();

        $response = $handler->handle($request);

        $this->assertInstanceOf(ResponseInterface::class, $response);

        $this->assertEquals('', $response->getBody()->__toString());
    }

    public function testMiddleWare()
    {
        $middlewares = [
            NotFoundMiddleware::class,
            JsonResponseMiddleware::class,
        ];

        $handler = new RequestHandler();
        $handler->addMiddlewares($middlewares);

        $request = ServerRequest::fromGlobals();

        $response = $handler->handle($request);

        $this->assertEquals(
            json_encode(JsonResponseMiddleware::$message),
            $response->getBody()->__toString()
        );

        $this->assertEquals(
            404,
            $response->getStatusCode()
        );

    }
}
