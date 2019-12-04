<?php

namespace Amber\Http\Server;

use Amber\Collection\Collection;
use Amber\Http\Message\Utils\RequestMethodInterface;
use Amber\Http\Message\Utils\StatusCodeInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Amber\Http\Server\Traits\ResponseFactoryTrait;

class RequestHandler implements RequestHandlerInterface, RequestMethodInterface, StatusCodeInterface
{
    use ResponseFactoryTrait;

    /**
     * @var Collection
     */
    protected $middlewares = [];

    /**
     * @var int
     */
    protected $index = 0;

    public function __construct(
        array $middlewares = []
    ) {
        $this->middlewares = new Collection($middlewares);
    }

    /**
     * Handles a request and produces a response.
     *
     * May call other collaborating code to generate the response.
     */
    public function handle(Request $request): Response
    {
        $id = $this->next();

        if ($this->hasMiddleware($id)) {
            return $this->getMiddleware($id)->process($request, $this);
        } else {
            return $this->responseFactory()->ok();
        }

        return $response;
    }

    protected function next(): int
    {
        $current = $this->index;

        $this->index++;

        return $current;
    }

    public function addMiddleware(string $middleware)
    {
        $this->middlewares = $this->middlewares->append($middleware);
    }

    public function hasMiddleware(string $middleware)
    {
        return $this->middlewares->has($middleware);
    }

    protected function getMiddleware($index): MiddlewareInterface
    {
        $middleware = $this->middlewares->get($index);

        return new $middleware();
    }

    public function addMiddlewares(iterable $middlewares = [])
    {
        foreach ($middlewares as $middleware) {
            $this->addMiddleware($middleware);
        }
    }
}
