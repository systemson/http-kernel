<?php

namespace Amber\Http\Server\Traits;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Amber\Http\Message\ResponseFactory;

trait ResponseFactoryTrait
{
    /**
     * @var ResponseFactoryInterface
     */
    protected $responseFactory;

    /**
     * Create a new response.
     *
     * @param int $code HTTP status code; defaults to 200.
     * @param string $reasonPhrase Reason phrase to associate with status code.
     *
     * @return ResponseInterface
     */
    public function response(int $code = ResponseFactory::STATUS_OK, string $reasonPhrase = null): ResponseInterface
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
        if ($this->responseFactory instanceof ResponseFactoryInterface) {
            return $this->responseFactory;
        }

        return new ResponseFactory();
    }
}
