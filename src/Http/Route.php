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
 * Route
 */
class Route {

    /**
     * @var string URL pattern
     */
    protected $pattern;

    /**
     * @var mixed Callback function
     */
    protected $callback;

    /**
     * @var array HTTP methods
     */
    protected $methods = array();

    /**
     * @var array Route parameters
     */
    protected $params = array();

    /**
     * @var array[Callable] Middleware to be run before only this route instance
     */
    protected $middleware = array();

    /**
     * @var bool Case-sensitive in match URL
     */
    protected $caseSensitive;

    /**
     * Constructor
     *
     * @param array   $methods       HTTP methods
     * @param string  $pattern       URL pattern
     * @param mixed   $callback      Callback function
     * @param boolean $caseSensitive Case-sensitive in match URL
     */
    public function __construct($methods, $pattern, $callback, $caseSensitive = false) {
        $this->pattern = $pattern;
        $this->setCallback($callback);
        $this->methods = $methods;
        $this->caseSensitive = $caseSensitive;
    }

    /**
     * Set route callback
     * 
     * USAGE:
     * 
     * setCallback('functionName')                          //Function name
     * setCallback('\Upfor\Request@getMethod')              //Object method
     * setCallback(array($requestObject, 'getMethod'))      //Object method
     * setCallback('\Upfor\Request::getMethod')             //Static method
     * setCallback(array('\Upfor\Request', 'getMethod'))    //Static method
     * setCallback(function($params) {                      //Closures
     *     //Business code...
     * })
     * 
     * @param  mixed $callback
     * @throws InvalidArgumentException If argument is not callable
     */
    public function setCallback($callback) {
        if (is_string($callback) && strpos($callback, '@')) {
            list($class, $method) = explode('@', $callback);
            $callback = function() use ($class, $method) {
                static $obj = null;
                if ($obj === null) {
                    $obj = new $class;
                }
                return call_user_func_array(array($obj, $method), func_get_args());
            };
        }

        if (!is_callable($callback)) {
            throw new InvalidArgumentException('Route callable must be callable');
        }

        $this->callback = $callback;
    }

    /**
     * Set middleware
     *
     * @param  Callable|array[Callable] $middleware
     * @return \Upfor\Http\Route
     * @throws InvalidArgumentException If argument is not callable or not an array of callables.
     */
    public function setMiddleware($middleware) {
        if (is_callable($middleware)) {
            $this->middleware[] = $middleware;
        } elseif (is_array($middleware)) {
            foreach ($middleware as $callable) {
                if (!is_callable($callable)) {
                    throw new InvalidArgumentException('All Route middleware must be callable');
                }
            }
            $this->middleware = array_merge($this->middleware, $middleware);
        } else {
            throw new InvalidArgumentException('Route middleware must be callable or an array of callables');
        }

        return $this;
    }

    /**
     * Get route parameters
     * 
     * @return array
     */
    public function getParams() {
        return $this->params;
    }

    /**
     * Set route parameters
     * 
     * @param  array $params
     */
    public function setParams($params) {
        $this->params = $params;
    }

    /**
     * Get route parameter value
     * 
     * @param  string $index Name of URL parameter
     * @return string
     * @throws InvalidArgumentException If route parameter does not exist at index
     */
    public function getParam($index) {
        if (!isset($this->params[$index])) {
            throw new InvalidArgumentException('Route parameter does not exist at specified index');
        }

        return $this->params[$index];
    }

    /**
     * Set route parameter value
     * 
     * @param  string $index Name of URL parameter
     * @param  mixed  $value The new parameter value
     * @throws InvalidArgumentException If route parameter does not exist at index
     */
    public function setParam($index, $value) {
        if (!isset($this->params[$index])) {
            throw new InvalidArgumentException('Route parameter does not exist at specified index');
        }
        $this->params[$index] = $value;
    }

    /**
     * Get supported HTTP methods
     * 
     * @return array
     */
    public function getHttpMethods() {
        return $this->methods;
    }

    /**
     * Detect support for an HTTP method
     *
     * @param  string  $method HTTP method
     * @return boolean Match status
     */
    public function matchMethod($method) {
        return in_array($method, $this->methods);
    }

    /**
     * Matches URL and parses parameters in the URL
     *
     * @param  string  $resourceUri Requested URL
     * @return boolean Match status
     */
    public function matchUrl($resourceUri) {
        $paramNames = array();
        $matchesCallback = function($matches) use (&$paramNames) {
            $paramNames[] = $matches[1];
            if (isset($matches[3])) {
                return '(?P<' . $matches[1] . '>' . $matches[3] . ')';
            }
            return '(?P<' . $matches[1] . '>[^/\?]+)';
        };

        $lastChar = substr($this->pattern, -1);
        $patternAsRegex = preg_replace_callback('#@([\w]+)(:([^/\(\)]*))?#', $matchesCallback, str_replace(')', ')?', (string) $this->pattern));

        // Fix trailing slash
        if ($lastChar === '/') {
            $patternAsRegex .= '?';
        }
        // Allow trailing slash
        else {
            $patternAsRegex .= '/?';
        }

        $regex = '#^' . $patternAsRegex . '$#';

        if ($this->caseSensitive === false) {
            $regex .= 'i';
        }

        //Cache URL params' names and values if this route matches the current HTTP request
        if (!preg_match($regex, str_replace('//', '/', $resourceUri), $paramValues)) {
            return false;
        }

        foreach ($paramNames as $name) {
            if (isset($paramValues[$name])) {
                $this->params[$name] = urldecode($paramValues[$name]);
            }
        }

        return true;
    }

    /**
     * Dispatch route
     *
     * @param object $app App instance
     * @return mixed
     */
    public function dispatch($app) {
        foreach ($this->middleware as $mw) {
            call_user_func_array($mw, array($this));
        }

        $params = array_values($this->getParams());
        array_push($params, $app);
        $return = call_user_func_array($this->callback, $params);
        return ($return === false) ? false : true;
    }

}
