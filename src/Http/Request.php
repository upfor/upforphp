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

/**
 * HTTP Request
 */
class Request {

    const METHOD_HEAD = 'HEAD';
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_PATCH = 'PATCH';
    const METHOD_DELETE = 'DELETE';
    const METHOD_OPTIONS = 'OPTIONS';

    /**
     * Constructor
     */
    public function __construct() {
        $this->env = filter_input_array(INPUT_SERVER);
    }

    /**
     * Get HTTP method
     * 
     * @return string
     */
    public function getMethod() {
        return $this->getVar('REQUEST_METHOD');
    }

    /**
     * Is this an AJAX request?
     * 
     * @return bool
     */
    public function isAjax() {
        if ($this->getVar('X_REQUESTED_WITH') == 'XMLHttpRequest') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Is this a GET request?
     * 
     * @return bool
     */
    public function isGet() {
        return $this->getMethod() === self::METHOD_GET;
    }

    /**
     * Is this a POST request?
     * 
     * @return bool
     */
    public function isPost() {
        return $this->getMethod() === self::METHOD_POST;
    }

    /**
     * Is this a PUT request?
     * 
     * @return bool
     */
    public function isPut() {
        return $this->getMethod() === self::METHOD_PUT;
    }

    /**
     * Is this a PATCH request?
     * 
     * @return bool
     */
    public function isPatch() {
        return $this->getMethod() === self::METHOD_PATCH;
    }

    /**
     * Is this a DELETE request?
     * 
     * @return bool
     */
    public function isDelete() {
        return $this->getMethod() === self::METHOD_DELETE;
    }

    /**
     * Is this a HEAD request?
     * 
     * @return bool
     */
    public function isHead() {
        return $this->getMethod() === self::METHOD_HEAD;
    }

    /**
     * Is this a OPTIONS request?
     * 
     * @return bool
     */
    public function isOptions() {
        return $this->getMethod() === self::METHOD_OPTIONS;
    }

    /**
     * Is the request from CLI (Command-Line Interface)?
     *
     * @return boolean
     */
    public function isCli() {
        return !$this->getVar('HTTP_HOST');
    }

    /**
     * Fetch GET data
     * 
     * @param  string           $key
     * @param  mixed            $default 默认数据
     * @return array|mixed|null
     */
    public function get($key = null, $default = null) {
        if ($key) {
            if (($value = filter_input(INPUT_GET, $key)) !== null) {
                return $value;
            } else {
                return $default;
            }
        } else {
            return filter_input_array(INPUT_GET);
        }
    }

    /**
     * Fetch POST data
     * 
     * @param  string           $key
     * @param  mixed            $default
     * @return array|mixed|null
     */
    public function post($key = null, $default = null) {
        if ($key) {
            if (($value = filter_input(INPUT_POST, $key)) !== null) {
                return $value;
            } else {
                return $default;
            }
        } else {
            return filter_input_array(INPUT_POST);
        }
    }

    /**
     * Fetch PUT data
     * 
     * @param  string           $key
     * @param  mixed            $default
     * @return array|mixed|null
     */
    public function put($key = null, $default = null) {
        return $this->post($key, $default);
    }

    /**
     * Fetch PATCH data
     * 
     * @param  string           $key
     * @param  mixed            $default
     * @return array|mixed|null
     */
    public function patch($key = null, $default = null) {
        return $this->post($key, $default);
    }

    /**
     * Fetch DELETE data
     * 
     * @param  string           $key
     * @param  mixed            $default
     * @return array|mixed|null
     */
    public function delete($key = null, $default = null) {
        return $this->post($key, $default);
    }

    /**
     * Fetch COOKIE data
     * 
     * @param  string            $key
     * @return array|string|null
     */
    public function cookie($key = null) {
        if ($key) {
            return filter_input(INPUT_COOKIE, $key);
        }

        return filter_input_array(INPUT_COOKIE);
    }

    /**
     * Get Body
     * Input stream (readable one time only; not available for multipart/form-data requests)
     * 
     * @link http://php.net/manual/zh/wrappers.php.php
     * @return string
     */
    public function getBody() {
        $input = file_get_contents('php://input');
        return $input ? $input : '';
    }

    /**
     * Get Content Type
     * 
     * @return string|null
     */
    public function getContentType() {
        return $this->getVar('CONTENT_TYPE');
    }

    /**
     * Get Media Type (type/subtype within Content Type header)
     * 
     * @return string|null
     */
    public function getMediaType() {
        $contentType = $this->getContentType();
        if ($contentType) {
            $contentTypeParts = preg_split('/\s*[;,]\s*/', $contentType);

            return strtolower($contentTypeParts[0]);
        }

        return null;
    }

    /**
     * Get Content Charset
     * 
     * @return string|null
     */
    public function getContentCharset() {
        return $this->getVar('HTTP_ACCEPT_CHARSET');
    }

    /**
     * Get Content-Length
     * 
     * @return int
     */
    public function getContentLength() {
        return $this->getVar('CONTENT_LENGTH', 0);
    }

    /**
     * Get Host
     * 
     * @return string
     */
    public function getHost() {
        if ($this->getVar('HTTP_X_FORWARDED_HOST')) {
            $host = $this->getVar('HTTP_X_FORWARDED_HOST');
        } elseif ($this->getVar('HTTP_HOST')) {
            $host = $this->getVar('HTTP_HOST');
        }

        if ($host) {
            if (strpos($host, ':') !== false) {
                $hostParts = explode(':', $host);

                return $hostParts[0];
            }

            return $host;
        }

        return $this->getVar('SERVER_NAME');
    }

    /**
     * Get Port
     * 
     * @return int
     */
    public function getPort() {
        return (int) $this->getVar('SERVER_PORT', 80);
    }

    /**
     * Get Host with Port
     * 
     * @return string
     */
    public function getHostWithPort() {
        return sprintf('%s:%s', $this->getHost(), $this->getPort());
    }

    /**
     * Request subdomain
     *
     * @return String request subdomain
     */
    public function subdomain() {
        $parts = explode('.', $this->host());
        $count = count($parts);
        return ($count > 2 ? $parts[0] : false);
    }

    /**
     * Get Scheme (https or http)
     * 
     * @return string
     */
    public function getScheme() {
        return !$this->getVar('HTTPS') || $this->getVar('HTTPS') === 'off' ? 'http' : 'https';
    }

    /**
     * Get Protocol
     * 
     * @return string
     */
    public function getProtocol() {
        return $this->getVar('SERVER_PROTOCOL') ? $this->getVar('SERVER_PROTOCOL') : 'HTTP/1.1';
    }

    /**
     * Get Script Name (physical path)
     * 
     * @return string
     */
    public function getScriptName() {
        if ($this->getVar('SCRIPT_NAME')) {
            return $this->getVar('SCRIPT_NAME');
        } elseif ($this->getVar('ORIG_SCRIPT_NAME')) {
            return $this->getVar('ORIG_SCRIPT_NAME');
        }

        return '';
    }

    /**
     * Get Request Uri
     * 
     * @return string
     */
    public function getRequestUri() {
        $requestUri = '';
        if ($this->getVar('HTTP_X_REWRITE_URL')) {
            $requestUri = $this->getVar('HTTP_X_REWRITE_URL');
        } elseif ($this->getVar('REQUEST_URI')) {
            $requestUri = $this->getVar('REQUEST_URI');
        } elseif ($this->getVar('ORIG_PATH_INFO')) {
            $requestUri = $this->getVar('ORIG_PATH_INFO') . ($this->getVar('QUERY_STRING') ? '?' . $this->getVar('QUERY_STRING') : '');
        }

        return $requestUri;
    }

    /**
     * Get Path Info (virtual path)
     * 
     * @return string
     */
    public function getPathInfo() {
        $pathInfo = '';
        if ($this->getVar('PATH_INFO')) {
            $pathInfo = $this->getVar('PATH_INFO');
        } elseif ($this->getVar('ORIG_PATH_INFO')) {
            $pathInfo = $this->getVar('ORIG_PATH_INFO');
            $scriptName = $this->getScriptName();
            if (substr($scriptName, -1, 1) == '/') {
                $pathInfo = $pathInfo . '/';
            }
        } else {
            $scriptName = $this->getScriptName();
            $scriptDir = preg_replace('/[^\/]+$/', '', $scriptName);
            $requestUri = $this->getRequestUri();
            $urlInfo = parse_url($requestUri);
            if (strpos($urlInfo['path'], $scriptName) === 0) {
                $pathInfo = substr($urlInfo['path'], strlen($scriptName));
            } elseif (strpos($urlInfo['path'], $scriptDir) === 0) {
                $pathInfo = substr($urlInfo['path'], strlen($scriptDir));
            }
        }
        if ($pathInfo) {
            $pathInfo = "/" . ltrim($pathInfo, "/");
        }
        return $pathInfo;
    }

    /**
     * Get Path (physical path + virtual path)
     * 
     * @return string
     */
    public function getPath() {
        return $this->getScriptName() . $this->getPathInfo();
    }

    /**
     * Get URL (scheme + host [ + port if non-standard ])
     * 
     * @return string
     */
    public function getUrl() {
        $url = $this->getScheme() . '://' . $this->getHost();
        if (($this->getScheme() === 'https' && $this->getPort() !== 443) || ($this->getScheme() === 'http' && $this->getPort() !== 80)) {
            $url .= sprintf(':%s', $this->getPort());
        }

        return $url;
    }

    /**
     * Get Proxy Ip
     * 
     * @return string
     */
    public function getProxyIp() {
        $keys = array(
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
        );

        $flags = \FILTER_FLAG_NO_PRIV_RANGE | \FILTER_FLAG_NO_RES_RANGE;
        foreach ($keys as $key) {
            if ($this->getVar($key) && filter_var($this->getVar($key), \FILTER_VALIDATE_IP, $flags) !== false) {
                return $this->getVar($key);
            }
        }

        return '';
    }

    /**
     * Get Referrer
     * 
     * @return string|null
     */
    public function getReferrer() {
        return $this->getVar('HTTP_REFERER');
    }

    /**
     * Get User Agent
     * 
     * @return string|null
     */
    public function getUserAgent() {
        return $this->getVar('HTTP_USER_AGENT');
    }

    /**
     * Gets a variable from $_SERVER using $default if not provided.
     *
     * @param   string  $var        Variable name
     * @param   string  $default    Default value to substitute
     * @return  string              Server variable value
     */
    public function getVar($var, $default = '') {
        return isset($this->env[$var]) ? $this->env[$var] : $default;
    }

}
