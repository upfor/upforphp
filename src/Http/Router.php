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

use ReflectionClass;
use InvalidArgumentException;
use Upfor\Http\Route;
use Upfor\Http\Request;

/**
 * Router
 */
class Router {

    /**
     * Mapped route
     *
     * @var array
     */
    protected $routes = array();

    /**
     * @var array Array of route objects that match the request URI (lazy-loaded)
     */
    protected $matchedRoutes = array();

    /**
     * Support HTTP methods
     * 
     * @var array
     */
    protected $supportMethods = array();

    public function __construct(Request $request) {
        //Get support methods from Request
        $requestReflection = new ReflectionClass($request);
        $constants = $requestReflection->getConstants();
        array_walk($constants, function (&$method) {
            if (strpos($method, 'METHOD_') !== 0) {
                unset($method);
            }
        });

        $this->supportMethods = array_values($constants);
    }

    /**
     * Get support methods
     * 
     * @return array
     */
    public function getSupportMethods() {
        return $this->supportMethods;
    }

    /**
     * Get mapped route objects
     *
     * @return array Array of routes
     */
    public function getRoutes() {
        return $this->routes;
    }

    /**
     * Add route
     *
     * @param  array    $methods  Array of HTTP methods
     * @param  string   $pattern  The route pattern
     * @param  callable $callback The route callable
     *
     * @return RouteInterface
     *
     * @throws InvalidArgumentException if the route pattern isn't a string
     */
    public function map($methods, $pattern, $callback) {
        if (!is_string($pattern)) {
            throw new InvalidArgumentException('Route pattern must be a string');
        }

        // According to RFC methods are defined in uppercase (See RFC 7231)
        $methods = array_map('strtoupper', $methods);
        $methods = array_intersect($this->getSupportMethods(), $methods);

        // Add route
        $route = new Route($methods, $pattern, $callback);
        $this->routes[] = $route;

        return $route;
    }

    /**
     * Return route objects that match the given HTTP method and URI
     * 
     * @param  string  $httpMethod  The HTTP method to match against
     * @param  string  $resourceUri The resource URI to match against
     * @param  boolean $reload      Should matching routes be re-parsed?
     * @return array[\Upfor\Http\Route]
     */
    public function getMatchedRoutes($httpMethod, $resourceUri) {
        foreach ($this->routes as $route) {
            if (!$route->matchMethod($httpMethod)) {
                continue;
            }

            if ($route->matchUrl($resourceUri)) {
                $this->matchedRoutes[] = $route;
            }
        }

        return $this->matchedRoutes;
    }

}
