<?php

namespace Amber\Http\Server\Middleware;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Message\ResponseFactoryInterface;
use Amber\Http\Message\ResponseFactory;

/**
 * Participant in processing a server request and response.
 *
 * An HTTP middleware component participates in processing an HTTP message:
 * by acting on the request, generating the response, or forwarding the
 * request to a subsequent middleware and possibly acting on its response.
 */
abstract class RequestMiddleware implements MiddlewareInterface
{
    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     *
     * @return Response
     */
    abstract public function process(Request $request, Handler $handler): Response;

    /**
     * Create a new response.
     *
     * @param int $code HTTP status code; defaults to 200.
     * @param string $reasonPhrase Reason phrase to associate with status code.
     *
     * @return Response
     */
    protected function createResponse(int $code = 200, string $reasonPhrase = ''): Response
    {
        return $this->responseFactory()->createResponse($code, $reasonPhrase);
    }

    /**
     * Returns a instance of ResponseFactory.
     *
     * @return ResponseFactoryInterface
     */
    public function responseFactory(): ResponseFactoryInterface
    {
        if ($this->factory instanceof ResponseFactoryInterface) {
            return $this->factory;
        }

        return new ResponseFactory();
    }
}