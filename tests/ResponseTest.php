<?php

namespace Tests;

use Amber\Http\Message\ResponseFactory;
use Amber\Http\Message\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    use MessageTestTrait;

    protected $message = Response::class;

    public function testResponse()
    {
        $response = new Response();

        $response = $response->withStatus(ResponseFactory::STATUS_NOT_FOUND);

        $this->assertEquals(ResponseFactory::STATUS_NOT_FOUND, $response->getStatusCode());
    }

    public function testResponseFactory()
    {
        $factory = new ResponseFactory();

        $response = $factory->createResponse();
        $this->assertEquals(ResponseFactory::STATUS_OK, $response->getStatusCode());
        $this->assertEquals(ResponseFactory::REASON_PHRASE[ResponseFactory::STATUS_OK], $response->getReasonPhrase());

        $response = $factory->ok();
        $this->assertEquals(ResponseFactory::STATUS_OK, $response->getStatusCode());
        $this->assertEquals(ResponseFactory::REASON_PHRASE[ResponseFactory::STATUS_OK], $response->getReasonPhrase());

        $response = $factory->created();
        $this->assertEquals(ResponseFactory::STATUS_CREATED, $response->getStatusCode());
        $this->assertEquals(ResponseFactory::REASON_PHRASE[ResponseFactory::STATUS_CREATED], $response->getReasonPhrase());

        $response = $factory->badRequest();
        $this->assertEquals(ResponseFactory::STATUS_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals(ResponseFactory::REASON_PHRASE[ResponseFactory::STATUS_BAD_REQUEST], $response->getReasonPhrase());

        $response = $factory->unauthorized();
        $this->assertEquals(ResponseFactory::STATUS_UNAUTHORIZED, $response->getStatusCode());
        $this->assertEquals(ResponseFactory::REASON_PHRASE[ResponseFactory::STATUS_UNAUTHORIZED], $response->getReasonPhrase());

        $response = $factory->forbidden();
        $this->assertEquals(ResponseFactory::STATUS_FORBIDDEN, $response->getStatusCode());
        $this->assertEquals(ResponseFactory::REASON_PHRASE[ResponseFactory::STATUS_FORBIDDEN], $response->getReasonPhrase());

        $response = $factory->notFound();
        $this->assertEquals(ResponseFactory::STATUS_NOT_FOUND, $response->getStatusCode());
        $this->assertEquals(ResponseFactory::REASON_PHRASE[ResponseFactory::STATUS_NOT_FOUND], $response->getReasonPhrase());

        $response = $factory->methodNotAllowed();
        $this->assertEquals(ResponseFactory::STATUS_METHOD_NOT_ALLOWED, $response->getStatusCode());
        $this->assertEquals(ResponseFactory::REASON_PHRASE[ResponseFactory::STATUS_METHOD_NOT_ALLOWED], $response->getReasonPhrase());

        $response = $factory->unprocessableEntity();
        $this->assertEquals(ResponseFactory::STATUS_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertEquals(ResponseFactory::REASON_PHRASE[ResponseFactory::STATUS_UNPROCESSABLE_ENTITY], $response->getReasonPhrase());

        $response = $factory->tooManyRequest();
        $this->assertEquals(ResponseFactory::STATUS_TOO_MANY_REQUESTS, $response->getStatusCode());
        $this->assertEquals(ResponseFactory::REASON_PHRASE[ResponseFactory::STATUS_TOO_MANY_REQUESTS], $response->getReasonPhrase());

        $response = $factory->internalServerError();
        $this->assertEquals(ResponseFactory::STATUS_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertEquals(ResponseFactory::REASON_PHRASE[ResponseFactory::STATUS_INTERNAL_SERVER_ERROR], $response->getReasonPhrase());

        $response = $factory->badGateway();
        $this->assertEquals(ResponseFactory::STATUS_BAD_GATEWAY, $response->getStatusCode());
        $this->assertEquals(ResponseFactory::REASON_PHRASE[ResponseFactory::STATUS_BAD_GATEWAY], $response->getReasonPhrase());

        $response = $factory->serviceUnavailable();
        $this->assertEquals(ResponseFactory::STATUS_SERVICE_UNAVAILABLE, $response->getStatusCode());
        $this->assertEquals(ResponseFactory::REASON_PHRASE[ResponseFactory::STATUS_SERVICE_UNAVAILABLE], $response->getReasonPhrase());

        $response = $factory->gatewayTimeout();
        $this->assertEquals(ResponseFactory::STATUS_GATEWAY_TIMEOUT, $response->getStatusCode());
        $this->assertEquals(ResponseFactory::REASON_PHRASE[ResponseFactory::STATUS_GATEWAY_TIMEOUT], $response->getReasonPhrase());
    }

    public function testJsonResponse()
    {
        $factory = new ResponseFactory();

        $data = [
            'status' => 'success',
            'message' => 'Some ramdon message.',
        ];

        $json = json_encode($data);

        $response = $factory->json($data);

        $this->assertEquals($json, $response->getBody()->__toString());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
    }

    public function testRedirect()
    {
        $factory = new ResponseFactory();
        $url = 'https://google.com';

        $response = $factory->redirect($url);

        $this->assertEquals(ResponseFactory::STATUS_SEE_OTHER, $response->getStatusCode());
        $this->assertEquals(ResponseFactory::REASON_PHRASE[ResponseFactory::STATUS_SEE_OTHER], $response->getReasonPhrase());

        $this->assertEquals($url, $response->getHeaderLine('Location'));

        $_SERVER['HTTP_REFERER'] = 'http://localhost/api/test';

        $response = $factory->redirectBack();

        $this->assertEquals(ResponseFactory::STATUS_SEE_OTHER, $response->getStatusCode());
        $this->assertEquals(ResponseFactory::REASON_PHRASE[ResponseFactory::STATUS_SEE_OTHER], $response->getReasonPhrase());

        $this->assertEquals($_SERVER['HTTP_REFERER'], $response->getHeaderLine('Location'));
    }
}
