<?php

namespace Tests;

use Amber\Http\Message\ServerRequest;

class RequestTest extends TestCase
{
    public function testBasic()
    {
        $request = new ServerRequest();
    }
}
