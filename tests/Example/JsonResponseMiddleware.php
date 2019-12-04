<?php

namespace Tests\Example;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Amber\Http\Server\Middleware\RequestMiddleware;

class JsonResponseMiddleware extends RequestMiddleware
{
	public static $message = [
    	'status' => 'fail',
	];

    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     *
     * @return Response
     */
    public function process(Request $request, Handler $handler): Response
    {
    	$response = $handler->handle($request);

    	$response->getBody()->write(json_encode(self::$message));

    	return $response;
    }
}