<?php

namespace Amber\Http\Message;

use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Amber\Http\Message\Traits\ClonableTrait;
use Amber\Collection\Collection;
use Amber\Phraser\Phraser;

/**
 * Value object representing a URI.
 *
 * This interface is meant to represent URIs according to RFC 3986 and to
 * provide methods for most common operations. Additional functionality for
 * working with URIs can be provided on top of the interface or externally.
 * Its primary use is for HTTP requests, but may also be used in other
 * contexts.
 *
 * Instances of this interface are considered immutable; all methods that
 * might change state MUST be implemented such that they retain the internal
 * state of the current instance and return an instance that contains the
 * changed state.
 *
 * Typically the Host header will be also be present in the request message.
 * For server-side requests, the scheme will typically be discoverable in the
 * server parameters.
 *
 * @link http://tools.ietf.org/html/rfc3986 (the URI specification)
 */
class Uri implements UriInterface
{
    use ClonableTrait;

    /**
     * @var string $scheme
     */
    protected $scheme;

    /**
     * @var string $host
     */
    protected $host;

    /**
     * @var string $user
     */
    protected $user;

    /**
     * @var string $pass
     */
    protected $pass;

    /**
     * @var int $port
     */
    protected $port;

    /**
     * @var string $path
     */
    protected $path;

    /**
     * @var array $query
     */
    public $query = [];

    /**
     * @var array $fragment
     */
    protected $fragment;

    /**
     * An array pairing schmeme and its default port.
     */
    const DEFAULT_PORT = [
        'http' => 80,
        'https' => 443,
    ];

    /**
     * Creates a new instance.
     *
     * @param string $uri The uri string.
     */
    public function __construct(string $uri = '')
    {
        if ($uri != '') {
            //$array = parse_url($uri);

            \extract(parse_url($uri));

            $this->scheme = $scheme ?? '';
            $this->host = $host ?? '';
            $this->port = $port ?? '';
            $this->user = $user ?? '';
            $this->pass = $pass ?? '';
            $this->path = $path ?? '';
            parse_str($query = '', $this->query);
            $this->fragment = $fragment ?? '';
        }
    }

    /**
     * Creates a new instance from $_SERVER vars.
     *
     * @return UriInterface
     */
    public static function fromGlobals(): UriInterface
    {
        $server = new Collection($_SERVER);

        $components = self::getComponentsFromServerParams($server);

        return self::fromComponents($components);
    }

    /**
     * Creates a new instance from a uri string.
     *
     * @param string $uri The uri string.
     *
     * @return UriInterface
     */
    public static function fromString(string $uri = ''): UriInterface
    {
        $components = parse_url($uri);

        return self::fromComponents($components ?? []);
    }

    /**
     * Creates a new instance from a PSR RequestInterface.
     *
     * @param Request $request
     *
     * @return UriInterface
     */
    public static function fromRequest(Request $request): UriInterface
    {
        $server = new Collection($request->getServerParams());

        $components = self::getComponentsFromServerParams($server);

        return self::fromComponents($components);
    }

    /**
     * Get the uri components from Server params.
     *
     * @param CollectionInterface $server
     *
     * @return array
     */
    protected static function getComponentsFromServerParams($server): array
    {
        return [
            'scheme' => strtolower(current(explode('/', $server->get('SERVER_PROTOCOL') ??  ''))),
            'host' => $server->get('HTTP_HOST'),
            'port' => $server->get('SERVER_PORT'),
            'path' => explode('?', $server->get('REQUEST_URI'))[0],
            'query' => $server->get('QUERY_STRING'),
        ];
    }

    /**
     * Creates a new instance from a components array.
     *
     * @param array $components
     *
     * @return UriInterface
     */
    public static function fromComponents(array $components = []): UriInterface
    {
        \extract($components);

        $uri = new static();

        $uri->scheme = $scheme ?? '';
        $uri->host = $host ?? '';
        $uri->user = $user ?? '';
        $uri->pass = $pass ?? '';
        $uri->port = $port ?? '';
        $uri->path = $path ?? '';
        parse_str($query ?? '', $uri->query);
        $uri->fragment = $fragment ?? '';

        return $uri;
    }

    /**
     * Retrieve the scheme component of the URI.
     *
     * If no scheme is present, this method MUST return an empty string.
     *
     * The value returned MUST be normalized to lowercase, per RFC 3986
     * Section 3.1.
     *
     * The trailing ":" character is not part of the scheme and MUST NOT be
     * added.
     *
     * @see    https://tools.ietf.org/html/rfc3986#section-3.1
     * @return string The URI scheme.
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Return an instance with the specified scheme.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified scheme.
     *
     * Implementations MUST support the schemes "http" and "https" case
     * insensitively, and MAY accommodate other schemes if required.
     *
     * An empty scheme is equivalent to removing the scheme.
     *
     * @param  string $scheme The scheme to use with the new instance.
     * @return static A new instance with the specified scheme.
     * @throws \InvalidArgumentException for invalid or unsupported schemes.
     */
    public function withScheme($scheme)
    {
        $new = $this->clone();

        $new->scheme = strtolower($scheme);

        return $new;
    }

    /**
     * Retrieve the authority component of the URI.
     *
     * If no authority information is present, this method MUST return an empty
     * string.
     *
     * The authority syntax of the URI is:
     *
     * <pre>
     * [user-info@]host[:port]
     * </pre>
     *
     * If the port component is not set or is the standard port for the current
     * scheme, it SHOULD NOT be included.
     *
     * @see    https://tools.ietf.org/html/rfc3986#section-3.2
     * @return string The URI authority, in "[user-info@]host[:port]" format.
     */
    public function getAuthority()
    {
        return (string) Phraser::make($this->getHost())
            ->prepend($this->getUserInfo() . '@', $this->getUserInfo())
            ->append(':' . $this->getPort(), $this->getPort())
        ;
    }

    /**
     * Retrieve the user information component of the URI.
     *
     * If no user information is present, this method MUST return an empty
     * string.
     *
     * If a user is present in the URI, this will return that value;
     * additionally, if the password is also present, it will be appended to the
     * user value, with a colon (":") separating the values.
     *
     * The trailing "@" character is not part of the user information and MUST
     * NOT be added.
     *
     * @return string The URI user information, in "username[:password]" format.
     */
    public function getUserInfo()
    {
        return (string) Phraser::make()
            ->append("{$this->user}", $this->user)
            ->append(":{$this->pass}", $this->pass)
        ;
    }

    /**
     * Return an instance with the specified user information.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified user information.
     *
     * Password is optional, but the user information MUST include the
     * user; an empty string for the user is equivalent to removing user
     * information.
     *
     * @param  string      $user     The user name to use for authority.
     * @param  null|string $password The password associated with $user.
     * @return static A new instance with the specified user information.
     */
    public function withUserInfo($user, $password = null)
    {
        $new = $this->clone();

        $new->user = $user;
        $new->pass = $password;

        return $new;
    }

    /**
     * Retrieve the host component of the URI.
     *
     * If no host is present, this method MUST return an empty string.
     *
     * The value returned MUST be normalized to lowercase, per RFC 3986
     * Section 3.2.2.
     *
     * @see    http://tools.ietf.org/html/rfc3986#section-3.2.2
     * @return string The URI host.
     */
    public function getHost()
    {
        return "{$this->host}";
    }

    /**
     * Return an instance with the specified host.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified host.
     *
     * An empty host value is equivalent to removing the host.
     *
     * @param  string $host The host-name to use with the new instance.
     * @return static A new instance with the specified host.
     * @throws \InvalidArgumentException for invalid host-names.
     */
    public function withHost($host)
    {
        $new = $this->clone();

        $new->host = $host;

        return $new;
    }

    /**
     * Retrieve the port component of the URI.
     *
     * If a port is present, and it is non-standard for the current scheme,
     * this method MUST return it as an integer. If the port is the standard port
     * used with the current scheme, this method SHOULD return null.
     *
     * If no port is present, and no scheme is present, this method MUST return
     * a null value.
     *
     * If no port is present, but a scheme is present, this method MAY return
     * the standard port for that scheme, but SHOULD return null.
     *
     * @return null|int The URI port.
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Return an instance with the specified port.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified port.
     *
     * Implementations MUST raise an exception for ports outside the
     * established TCP and UDP port ranges.
     *
     * A null value provided for the port is equivalent to removing the port
     * information.
     *
     * @param  null|int $port The port to use with the new instance; a null value
     *                        removes the port information.
     * @return static A new instance with the specified port.
     * @throws \InvalidArgumentException for invalid ports.
     */
    public function withPort($port)
    {
        $new = $this->clone();

        $new->port = (int) $port;

        return $new;
    }

    /**
     * Retrieve the path component of the URI.
     *
     * The path can either be empty or absolute (starting with a slash) or
     * rootless (not starting with a slash). Implementations MUST support all
     * three syntaxes.
     *
     * Normally, the empty path "" and absolute path "/" are considered equal as
     * defined in RFC 7230 Section 2.7.3. But this method MUST NOT automatically
     * do this normalization because in contexts with a trimmed base path, e.g.
     * the front controller, this difference becomes significant. It's the task
     * of the user to handle both "" and "/".
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.3.
     *
     * As an example, if the value should include a slash ("/") not intended as
     * delimiter between path segments, that value MUST be passed in encoded
     * form (e.g., "%2F") to the instance.
     *
     * @see    https://tools.ietf.org/html/rfc3986#section-2
     * @see    https://tools.ietf.org/html/rfc3986#section-3.3
     * @return string The URI path.
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Return an instance with the specified path.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified path.
     *
     * The path can either be empty or absolute (starting with a slash) or
     * rootless (not starting with a slash). Implementations MUST support all
     * three syntaxes.
     *
     * If the path is intended to be domain-relative rather than path relative then
     * it must begin with a slash ("/"). Paths not starting with a slash ("/")
     * are assumed to be relative to some base path known to the application or
     * consumer.
     *
     * Users can provide both encoded and decoded path characters.
     * Implementations ensure the correct encoding as outlined in getPath().
     *
     * @param  string $path The path to use with the new instance.
     * @return static A new instance with the specified path.
     * @throws \InvalidArgumentException for invalid paths.
     */
    public function withPath($path)
    {
        $new = $this->clone();

        $new->path = $path;

        return $new;
    }

    /**
     * Retrieve the query string of the URI.
     *
     * If no query string is present, this method MUST return an empty string.
     *
     * The leading "?" character is not part of the query and MUST NOT be
     * added.
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.4.
     *
     * As an example, if a value in a key/value pair of the query string should
     * include an ampersand ("&") not intended as a delimiter between values,
     * that value MUST be passed in encoded form (e.g., "%26") to the instance.
     *
     * @see    https://tools.ietf.org/html/rfc3986#section-2
     * @see    https://tools.ietf.org/html/rfc3986#section-3.4
     * @return string The URI query string.
     */
    public function getQuery()
    {
        if (empty($this->query)) {
            return '';
        }

        return preg_replace('/%5B[0-9]+%5D/simU', '[]', http_build_query($this->query));
    }

    /**
     * Return an instance with the specified query string.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified query string.
     *
     * Users can provide both encoded and decoded query characters.
     * Implementations ensure the correct encoding as outlined in getQuery().
     *
     * An empty query string value is equivalent to removing the query string.
     *
     * @param  string $query The query string to use with the new instance.
     * @return static A new instance with the specified query string.
     * @throws \InvalidArgumentException for invalid query strings.
     */
    public function withQuery($query)
    {
        $new = $this->clone();

        parse_str($query, $new->query);

        return $new;
    }

    /**
     * Retrieve the fragment component of the URI.
     *
     * If no fragment is present, this method MUST return an empty string.
     *
     * The leading "#" character is not part of the fragment and MUST NOT be
     * added.
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.5.
     *
     * @see    https://tools.ietf.org/html/rfc3986#section-2
     * @see    https://tools.ietf.org/html/rfc3986#section-3.5
     * @return string The URI fragment.
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * Return an instance with the specified URI fragment.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified URI fragment.
     *
     * Users can provide both encoded and decoded fragment characters.
     * Implementations ensure the correct encoding as outlined in getFragment().
     *
     * An empty fragment value is equivalent to removing the fragment.
     *
     * @param  string $fragment The fragment to use with the new instance.
     * @return static A new instance with the specified fragment.
     */
    public function withFragment($fragment)
    {
        $new = $this->clone();

        $new->fragment = $fragment;

        return $new;
    }

    /**
     * Return the string representation as a URI reference.
     *
     * @return string
     */
    public function toString(): string
    {
        return (string) Phraser::make('')
            ->append($this->getScheme() . '://', $this->getScheme())
            ->append($this->getAuthority() .  $this->getPath())
            ->append('?' . $this->getQuery(), $this->getQuery())
            ->append('#' . $this->getFragment(), $this->getFragment())
        ;
    }

    /**
     * Return the string representation as a URI reference.
     *
     * Depending on which components of the URI are present, the resulting
     * string is either a full URI or relative reference according to RFC 3986,
     * Section 4.1. The method concatenates the various components of the URI,
     * using the appropriate delimiters:
     *
     * - If a scheme is present, it MUST be suffixed by ":".
     * - If an authority is present, it MUST be prefixed by "//".
     * - The path can be concatenated without delimiters. But there are two
     *   cases where the path has to be adjusted to make the URI reference
     *   valid as PHP does not allow to throw an exception in __toString():
     *     - If the path is rootless and an authority is present, the path MUST
     *       be prefixed by "/".
     *     - If the path is starting with more than one "/" and no authority is
     *       present, the starting slashes MUST be reduced to one.
     * - If a query is present, it MUST be prefixed by "?".
     * - If a fragment is present, it MUST be prefixed by "#".
     *
     * @see    http://tools.ietf.org/html/rfc3986#section-4.1
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }
}
