<?php

namespace Tests;

use Amber\Http\Message\ServerRequest;
use Amber\Http\Message\Uri;
use PHPUnit\Framework\TestCase;

class UriTest extends TestCase
{
    public function testEmptyUri()
    {
        $uri = new Uri();

        $this->assertEquals('', $uri->getScheme());
        $this->assertEquals('', $uri->getAuthority());
        $this->assertEquals('', $uri->getUserInfo());
        $this->assertEquals('', $uri->getHost());
        $this->assertEquals('', $uri->getPort());
        $this->assertEquals('', $uri->getPath());
        $this->assertEquals('', $uri->getQuery());
        $this->assertEquals('', $uri->getFragment());

        return $uri;
    }

    /**
     * @depends testEmptyUri
     */
    public function testSchemeAndPort(Uri $uri)
    {
        $uri = $uri->withScheme('http');
        $this->assertEquals('', $uri->getPort());
        $uri = $uri->withScheme('https');
        $this->assertEquals('', $uri->getPort());
        $uri = $uri->withPort(8000);
        $this->assertEquals(8000, $uri->getPort());
    }

    /**
     * @depends testEmptyUri
     */
    public function testHostUserInfoAndAuthority(Uri $uri)
    {
        $uri = $uri->withHost('localhost');
        $this->assertEquals('localhost', $uri->getHost());
        $this->assertEquals('localhost', $uri->getAuthority());
        $uri = $uri->withUserInfo('user');
        $this->assertEquals('user', $uri->getUserInfo());
        $this->assertEquals('user@localhost', $uri->getAuthority());
        $uri = $uri->withUserInfo('user', 'password');
        $this->assertEquals('user:password', $uri->getUserInfo());
        $this->assertEquals('user:password@localhost', $uri->getAuthority());
    }

    /**
     * @depends testEmptyUri
     */
    public function testPath(Uri $uri)
    {
        $uri = $uri->withPath('/test_url');
        $this->assertEquals('/test_url', $uri->getPath());
        $uri = $uri->withPath('test_url');
        $this->assertEquals('test_url', $uri->getPath());
    }

    /**
     * @depends testEmptyUri
     */
    public function testQueryString(Uri $uri)
    {
        $uri = $uri->withQuery('string=test');
        $this->assertEquals('string=test', $uri->getQuery());
        $uri = $uri->withQuery('array[]=one&array[]=two&array[]=three');
        $this->assertEquals('array[]=one&array[]=two&array[]=three', $uri->getQuery());
    }

    /**
     * @depends testEmptyUri
     */
    public function testFragment(Uri $uri)
    {
        $uri = $uri->withFragment('this_is_a_fragment');
        $this->assertEquals('this_is_a_fragment', $uri->getFragment());
        $uri = $uri->withHost('localhost');
        $this->assertEquals('localhost#this_is_a_fragment', (string) $uri);
    }

    public function testUriFromConstruct()
    {
        $uri = new Uri('https://user:password@localhost/relative/url#fragment');

        $this->assertEquals('https', $uri->getScheme());
        $this->assertEquals('user:password', $uri->getUserInfo());
        $this->assertEquals('user:password@localhost', $uri->getAuthority());
        $this->assertEquals('localhost', $uri->getHost());
        $this->assertEquals('', $uri->getPort());
        $this->assertEquals('/relative/url', $uri->getPath());
        $this->assertEquals('fragment', $uri->getFragment());

        $this->assertEquals('', (string) new Uri());
    }

    public function testUriFromString()
    {
        $uri = Uri::fromString('https://user:password@localhost/relative/url#fragment');

        $this->assertEquals('https', $uri->getScheme());
        $this->assertEquals('user:password', $uri->getUserInfo());
        $this->assertEquals('user:password@localhost', $uri->getAuthority());
        $this->assertEquals('localhost', $uri->getHost());
        $this->assertEquals('', $uri->getPort());
        $this->assertEquals('/relative/url', $uri->getPath());
        $this->assertEquals('fragment', $uri->getFragment());

        $this->assertEquals('', (string) Uri::fromString(''));
    }

    public function testUriFromRequest()
    {
        $uri = Uri::fromRequest(new ServerRequest());

        $this->assertEquals('', (string) $uri);
    }

    public function testUriFromGlobals()
    {
        $uri = Uri::fromGlobals();

        $this->assertEquals('', $uri->toString());
    }
}
