<?php

namespace Tests;

use Amber\Http\Message\ServerRequest;
use Amber\Http\Message\Uri;
use PHPUnit\Framework\TestCase;

class ServerRequestTest extends TestCase
{
    protected function mockGetRequest()
    {
        $_SERVER['SERVER_PROTOCOL'] = 'http/' . ServerRequest::PROTOCOL_VERSION;
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    protected function getParamsArray()
    {
        return [
            'param1' => 'value1',
            'param2' => 'value2',
        ];
    }

    public function testEmptyServerRequest()
    {
        $request = new ServerRequest();

        $this->assertEmpty($request->getServerParams());
        $this->assertEmpty($request->getCookieParams());
        $this->assertEmpty($request->getQueryParams());
        $this->assertEmpty($request->getUploadedFiles());
        $this->assertEmpty($request->getParsedBody());
        $this->assertEmpty($request->getAttributes());

        return $request;
    }

    /**
     * @depends testEmptyServerRequest
     */
    public function testWithCookieParams(ServerRequest $request)
    {
        $array = $this->getParamsArray();

        $request = $request->withCookieParams($array);
        $this->assertEquals($array, $request->getCookieParams());
    }

    /**
     * @depends testEmptyServerRequest
     */
    public function testWithQueryParams(ServerRequest $request)
    {
        $array = $this->getParamsArray();

        $request = $request->withQueryParams($array);
        $this->assertEquals($array, $request->getQueryParams());
    }

    /**
     * @depends testEmptyServerRequest
     */
    public function testWithUploadedFiles(ServerRequest $request)
    {
        $array = [
            'avatar' => 'image'
        ];

        $request = $request->withUploadedFiles($array);
        $this->assertEquals($array, $request->getUploadedFiles());
    }

    /**
     * @depends testEmptyServerRequest
     */
    public function testWithParsedBody(ServerRequest $request)
    {
        $array = $this->getParamsArray();

        $request = $request->withParsedBody($array);
        $this->assertEquals($array, $request->getParsedBody());
    }

    /**
     * @depends testEmptyServerRequest
     */
    public function testWithAndWithoutAttribute(ServerRequest $request)
    {
        $array = $this->getParamsArray();
        $names = array_keys($array);

        foreach ($array as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        $this->assertEquals($array, $request->getAttributes());
        $this->assertEquals($array[$names[0]], $request->getAttribute($names[0]));
        $this->assertEquals($array[$names[1]], $request->getAttribute($names[1]));
        $request->withoutAttribute($names[0]);
        
        unset($array[$names[0]]);
        $this->assertEquals($array, $request->getAttributes());
    }

    public function testFromGlobals()
    {
        $request = ServerRequest::fromGlobals();

        $this->assertInstanceOf(ServerRequest::class, $request);

        $this->assertEquals('1.1', $request->getProtocolVersion());
    }
}
