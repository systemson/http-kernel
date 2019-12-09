<?php

namespace Tests;

use Amber\Http\Message\Traits\MessageTrait;
use Amber\Http\Message\Request;
use Amber\Http\Message\Response;
use Psr\Http\Message\StreamInterface;
use Sunrise\Stream\StreamFactory;

trait MessageTestTrait
{
    protected function mockMessage()
    {
        return new $this->message;
    }

    public function testMessage()
    {
        $message = $this->mockMessage();

        $this->assertEquals('1.1', $message->getProtocolVersion());

        $message = $message->withProtocolVersion('1.0');

        $this->assertEquals('1.0', $message->getProtocolVersion());
    }

    public function testHeaders()
    {
        $message = $this->mockMessage();

        $this->assertEquals([], $message->getHeaders());

        $name = 'Test-Header';
        $value = 'Test';

        $message = $message->withHeader($name, $value);

        $this->assertTrue($message->hasHeader($name));
        $this->assertEquals([$value], $message->getHeader($name));

        $addedValues = [
            'test1',
            'test2',
        ];

        $message = $message->withAddedHeader($name, $addedValues);

        $allValues = array_merge((array) $value, $addedValues);

        $this->assertEquals($allValues, $message->getHeader($name));
        $this->assertEquals(implode(',', $allValues), $message->getHeaderLine($name));

        $message = $message->withoutHeader($name);
        $this->assertFalse($message->hasHeader($name));
        $this->assertEquals([], $message->getHeader($name));
        $this->assertEquals('', $message->getHeaderLine($name));
    }

    public function testBody()
    {
        $message = $this->mockMessage();

        $body = $message->getBody();

        $this->assertInstanceOf(StreamInterface::class, $body);
        $this->assertEquals('', $body->__toString());

        $array = ['key' => 'value'];

        $new = StreamFactory::createStream(json_encode($array));

        $message = $message->withBody($new);
        $this->assertEquals(json_encode($array), $message->getBody()->__toString());
    }
}
