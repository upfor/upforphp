<?php

/**
 * Upfor Framework
 *
 * @author      Shocker Li <shocker@upfor.club>
 * @copyright   Upfor Group
 * @link        http://framework.upfor.club
 * @license     MIT
 */

namespace Upfor\Http;

use InvalidArgumentException;

/**
 * Response
 */
class Response {

    /**
     * @var int HTTP status
     */
    protected $status = 200;

    /**
     * @var array HTTP headers
     */
    protected $headers = array();

    /**
     * @var string HTTP response body
     */
    protected $body = '';

    /**
     * @var string HTTP response cookies
     */
    protected $cookies = array();

    /**
     * @var array HTTP status codes
     */
    protected static $messages = array(
        //Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        //Successful 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        226 => 'IM Used',
        //Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        //Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        //Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    );

    /**
     * @var array Default cookie settings
     */
    protected $defaultCookieSettings = array(
        'value' => '',
        'expires' => 0,
        'domain' => null,
        'path' => null,
        'secure' => false,
        'httponly' => false
    );

    /**
     * Get and set status
     *
     * @param  int|null $code
     * @return int
     * @throws InvalidArgumentException
     */
    public function status($code = null) {
        if ($code === null) {
            return $this->status;
        }

        if (array_key_exists($code, self::$messages)) {
            $this->status = $code;
        } else {
            throw new InvalidArgumentException('Invalid status code');
        }
    }

    /**
     * Get and set header
     *
     * @param  string      $name  Header name
     * @param  string|null $value Header value
     * @return string      Header value
     */
    public function header($name, $value = null) {
        if (!$name) {
            throw new InvalidArgumentException('Invalid header name.');
        }
        if (!is_null($value)) {
            $this->headers[$name] = $value;
        }

        return $this->headers[$name];
    }

    /**
     * Writes content to the response body
     *
     * @param string $body    Response content
     * @param bool   $replace Overwrite existing response body
     * @return string         The updated HTTP response body
     */
    public function body($body, $replace = false) {
        if ($replace) {
            $this->body = $body;
        } else {
            $this->body .= (string) $body;
        }

        return $this->body;
    }

    /**
     * Set cookie
     * 
     * @param type $name  The name of the cookie
     * @param type $value If string, the value of cookie;
     *                    if array, properties for cookie including:
     *                          value, expire, path, domain, secure, httponly
     */
    public function setCookie($name, $value) {
        if (is_array($value)) {
            $this->cookies[$name] = array_replace($this->defaultCookieSettings, $value);
        } else {
            $this->cookies[$name] = array_replace($this->defaultCookieSettings, array('value' => $value));
        }
    }

    /**
     * Delete cookie
     * 
     * @param string $name The name of the cookie
     */
    public function deleteCookie($name) {
        $this->cookies[$name] = array_replace($this->defaultCookieSettings, array('value' => '', 'expires' => time() - 86400));
    }

    private function serializeCookies() {
        foreach ($this->cookies as $name => $value) {
            $domain = $path = $expires = $secure = $httponly = '';
            if (isset($value['domain']) && $value['domain']) {
                $domain = '; domain=' . $value['domain'];
            }
            if (isset($value['path']) && $value['path']) {
                $path = '; path=' . $value['path'];
            }
            if (isset($value['expires'])) {
                $timestamp = is_string($value['expires']) ? strtotime($value['expires']) : (int) $value['expires'];
                $timestamp && $expires = '; expires=' . gmdate('D, d-M-Y H:i:s e', $timestamp);
            }
            if (isset($value['secure']) && $value['secure']) {
                $secure = '; secure';
            }
            if (isset($value['httponly']) && $value['httponly']) {
                $httponly = '; HttpOnly';
            }
            $this->headers['Set-Cookie'][$name] = sprintf('%s=%s%s', urlencode($name), urlencode((string) $value['value']), $domain . $path . $expires . $secure . $httponly);
        }
    }

    /**
     * Clears the response
     */
    public function clear() {
        $this->status = 200;
        $this->headers = array();
        $this->cookies = array();
        $this->body = '';
    }

    /**
     * Sets caching headers for the response
     *
     * @param int|string $expires Expiration time
     */
    public function cache($expires) {
        if ($expires === false) {
            $this->headers['Expires'] = 'Wed, 06 Aug 1991 08:00:00 GMT';
            $this->headers['Cache-Control'] = array(
                'no-store, no-cache, must-revalidate',
                'post-check=0, pre-check=0',
                'max-age=0'
            );
            $this->headers['Pragma'] = 'no-cache';
        } else {
            $expires = is_int($expires) ? $expires : strtotime($expires);
            $this->headers['Expires'] = gmdate('D, d M Y H:i:s', $expires) . ' GMT';
            $this->headers['Cache-Control'] = 'max-age=' . ($expires - time());
            if (isset($this->headers['Pragma']) && $this->headers['Pragma'] == 'no-cache') {
                unset($this->headers['Pragma']);
            }
        }
    }

    /**
     * Sends HTTP headers
     */
    public function sendHeaders() {
        // Send status code header
        if (strpos(php_sapi_name(), 'cgi') !== false) {
            header(sprintf('Status: %s', $this->status, self::$messages[$this->status]), true);
        } else {
            header(sprintf('%s %d %s', (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1'), $this->status, self::$messages[$this->status]), true, $this->status);
        }

        // Send other headers
        foreach ($this->headers as $name => $value) {
            if (is_array($value)) {
                foreach ($value as $val) {
                    header("$name: $val", false);
                }
            } else {
                header("$name: $value", false);
            }
        }
    }

    /**
     * Sends a HTTP response
     */
    public function send() {
        if (!headers_sent()) {
            $this->serializeCookies();
            $this->sendHeaders();
        }

        echo $this->body;
    }

}
