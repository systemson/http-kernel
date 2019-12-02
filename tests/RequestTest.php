<?php

namespace Tests;

use Amber\Http\Message\Request;
use Amber\Http\Message\Uri;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    use MessageTestTrait;

    protected $message = Request::class;

    public function testRequest()
    {
        $request = new Request();

        $this->assertEquals('/', $request->getRequestTarget());

        $request = Request::fromGlobals();

        $this->assertEquals('/', $request->getRequestTarget());

        $url = 'localhost/api/lol';

        $request = $request->withRequestTarget($url);

        $this->assertEquals($url, $request->getRequestTarget());
    }

    public function testRequestMethod()
    {
        $request = Request::fromGlobals();

        $this->assertEquals('GET', $request->getMethod());

        $request = $request->withMethod('POST');

        $this->assertEquals('POST', $request->getMethod());
    }

    public function testRequestUri()
    {
        $request = Request::fromGlobals();

        $this->assertEquals('', $request->getUri()->toString());

        $uri = 'http://localhost/api/test';
        $request = $request->withUri(Uri::fromString($uri));

        $this->assertEquals($uri, $request->getUri()->toString());

        $newUri = 'http://example.com/api/other/test';
        $request = $request->withUri(Uri::fromString($newUri));

        $this->assertEquals($newUri, $request->getUri()->toString());

        $request = $request->withUri(Uri::fromString($uri));
        $request = $request->withUri(Uri::fromString($newUri), true);

        $this->assertEquals('http://localhost/api/other/test', $request->getUri()->toString());
    }
}
