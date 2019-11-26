<?php

namespace Tests;

use Amber\Http\Message\ServerRequest;
use Amber\Http\Message\Uri;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
	public function request(array $options = [])
	{
		extract($options);

        return new ServerRequest(
        	$version ?? '1.1',
        	$method ?? 'GET',
        	$uri ?? '/',
        	$headers ?? [],
        	$body ?? null,
        );
	}

    public function testMessage()
    {
        $request = $this->request();

        $this->assertEquals('1.1', $request->getProtocolVersion());
    }

    public function testRequest()
    {
        $request = $this->request();

        $this->assertEquals('/', $request->getRequestTarget());

        $this->assertEquals('GET', $request->getMethod());

        $this->assertEquals('/', (string) $request->getUri());
        $this->assertInstanceOf(Uri::class, $request->getUri());
    }

    public function testServerRequest()
    {
        $request = $this->request();

        //$this->assertEquals([], $request->getServerParams()->toArray());

        $this->assertEquals([], $request->getCookieParams()->toArray());
        $this->assertEquals([], $request->getQueryParams()->toArray());
        $this->assertEquals([], $request->getUploadedFiles()->toArray());
        $this->assertEquals([], $request->getParsedBody()->toArray());
        $this->assertEquals([], $request->getAttributes()->toArray());
    }
}
