<?php

namespace Amber\Http\Message\Traits;

trait RequestUtilsTrait
{
    protected static function getGlobalHeaders(): array
    {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }

        if (!isset($_SERVER)) {
            return [];
        }

        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }

        return $headers ?? [];
    }

    public function acceptsHtml()
    {
        return strpos($this->getHeaderLine('Accept'), 'html') !== false;
    }

    public function acceptsJson()
    {
        return strpos($this->getHeaderLine('Accept'), 'json') !== false;
    }

    public function acceptsXml()
    {
        return strpos($this->getHeaderLine('Accept'), 'xml') !== false;
    }
}
