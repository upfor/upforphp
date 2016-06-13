<?php

/**
 * Upfor Framework
 *
 * @author      Shocker Li <shocker@upfor.club>
 * @copyright   Upfor Group
 * @link        http://framework.upfor.club
 * @license     MIT
 */

namespace Upfor;

use Closure;
use Exception;
use Upfor\Helper\Collection;
use Upfor\Helper\Container;
use Upfor\Http\Request;
use Upfor\Http\Response;
use Upfor\Http\Router;
use Upfor\Exception\LogWriterInterface;
use Upfor\Exception\FileLogWriter;
use Upfor\Exception\Logger;
use Upfor\Exception\Error;

/**
 * App engine
 */
class App {

    /**
     * @const string
     */
    const VERSION = '0.0.7';

    /**
     * @var Container
     */
    private $container;

    /**
     * @var array[\Upfor\App]
     */
    protected static $apps = [];

    /**
     * @var string Current app name
     */
    protected $name = 'default';

    /**
     * @var array
     */
    protected $hooks = array(
        'upfor.before' => null,
        'upfor.before.dispatch' => null,
        'upfor.after.dispatch' => null,
        'upfor.after' => null,
    );

    /**
     * Constructor
     * 
     * @param array $settings
     */
    public function __construct($settings = []) {
        // Setup IoC container
        $this->container = new Container();
        $this->container['settings'] = new Collection(array_merge(static::getDefaultSettings(), $settings));
        $this->registerDefaultService();

        // Make default if first instance
        if (is_null(static::getInstance())) {
            $this->setName('default');
        }
    }

    /**
     * Register default services
     */
    protected function registerDefaultService() {
        // Default request
        $this->singleton('request', function($c) {
            return new Request();
        });

        // Default response
        $this->singleton('response', function($c) {
            return new Response();
        });

        // Default router
        $this->singleton('router', function($c) {
            return new Router($c['request']);
        });

        // Default log writer
        $this->singleton('logWriter', function ($c) {
            $logWriter = new $c['settings']['log.writer']['writer']($c['settings']['log.writer']['settings']);
            return is_object($logWriter) && ($logWriter instanceof LogWriterInterface) ? $logWriter : new FileLogWriter($c['settings']['log.writer']['settings']);
        });

        // Default log
        $this->singleton('logger', function($c) {
            $logger = new Logger($c['logWriter']);
            $logger->setEnabled($c['settings']['log.enabled']);

            return $logger;
        });

        $this->singleton('error', function($c) {
            $error = new Error($c['logger']);

            return $error;
        });
    }

    /**
     * Enable access to the DI container by consumers of $app
     *
     * @return Container
     */
    public function getContainer() {
        return $this->Container;
    }

    /**
     * Get default application settings
     * @return array
     */
    public static function getDefaultSettings() {
        return array(
            // Debugging
            'debug' => true,
            // Logging
            'log.writer' => ['writer' => '\Upfor\Exception\FileLogWriter', 'settings' => []],
            'log.enabled' => true,
            // Cookies
            'cookies.expires' => '30 minutes',
            'cookies.path' => '/',
            'cookies.domain' => null,
            'cookies.secure' => false,
            'cookies.httponly' => false,
            // Routing
            'routes.case_sensitive' => false,
        );
    }

    /**
     * Configure/Get Application Settings
     * 
     * @param  string|array $name  If a string, the name of the setting to set or retrieve. Else an associated array of setting names and values
     * @param  mixed        $value
     * @return mixed
     */
    public function config($name, $value = null) {
        $c = $this->container;

        if (is_array($name)) {
            if (true === $value) {
                $c['settings'] = array_merge_recursive($c['settings'], $name);
            } else {
                $c['settings'] = array_merge($c['settings'], $name);
            }
        } elseif (func_num_args() === 1) {
            return isset($c['settings'][$name]) ? $c['settings'][$name] : null;
        } else {
            $settings = $c['settings'];
            $settings[$name] = $value;
            $c['settings'] = $settings;
        }
    }

    /**
     * Set HTTP cookie to be sent with the HTTP response
     *
     * @param string     $name      The cookie name
     * @param string     $value     The cookie value
     * @param int|string $expires   The duration of the cookie;
     *                                  If integer, should be UNIX timestamp;
     *                                  If string, converted to UNIX timestamp with `strtotime`;
     * @param string     $path      The path on the server in which the cookie will be available on
     * @param string     $domain    The domain that the cookie is available to
     * @param bool       $secure    Indicates that the cookie should only be transmitted over a secure
     *                              HTTPS connection to/from the client
     * @param bool       $httponly  When TRUE the cookie will be made accessible only through the HTTP protocol
     */
    public function setCookie($name, $value, $expires = null, $path = null, $domain = null, $secure = null, $httponly = null) {
        $settings = array(
            'value' => $value,
            'expires' => is_null($expires) ? $this->config('cookies.expires') : $expires,
            'path' => is_null($path) ? $this->config('cookies.path') : $path,
            'domain' => is_null($domain) ? $this->config('cookies.domain') : $domain,
            'secure' => is_null($secure) ? $this->config('cookies.secure') : $secure,
            'httponly' => is_null($httponly) ? $this->config('cookies.httponly') : $httponly
        );
        $this->response->setCookie($name, $settings);
    }

    /**
     * Assign hook
     * 
     * @param  string   $name       The hook name
     * @param  mixed    $callable   A callable object
     */
    public function hook($name, $callable) {
        if (is_callable($callable)) {
            $this->hooks[$name] = $callable;
        }
    }

    /**
     * Invoke hook
     * 
     * @param  string   $name       The hook name
     * @param  mixed    $hookArg    (Optional) Argument for hooked functions
     */
    public function applyHook($name, $hookArg = null) {
        if (!$this->hooks[$name] || !is_callable($this->hooks[$name])) {
            return false;
        }
        call_user_func($this->hooks[$name], $hookArg);
    }

    /**
     * Get application instance by name
     * 
     * @param  string    $name The name of the Upfor application
     * @return \Upfor\App|null
     */
    public static function getInstance($name = 'default') {
        return isset(static::$apps[$name]) ? static::$apps[$name] : null;
    }

    /**
     * Set application name
     * 
     * @param  string $name The name of this application
     */
    public function setName($name) {
        $this->name = $name;
        static::$apps[$name] = $this;
    }

    /**
     * Get application name
     * 
     * @return string|null
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Redirect
     *
     * @param  string   $url        The destination URL
     * @param  int      $status     The HTTP redirect status code (optional)
     */
    public function redirect($url, $status = 302) {
        $this->response->clear();
        $this->response->status($status);
        $this->response->header('Location', $url);
    }

    /**
     * Run
     * 
     * @return void
     */
    public function run() {
        ini_set('display_errors', 0);
        if ($this->settings['debug']) {
            error_reporting(-1);
            ini_set('display_startup_errors', true);
            $this->error->isDisplay(true);
        }

        try {
            $this->applyHook('upfor.before');
            ob_start();
            $dispatched = false;
            $matchedRoutes = $this->router->getMatchedRoutes($this->request->getMethod(), $this->request->getPathInfo());

            foreach ($matchedRoutes as $route) {
                try {
                    $this->router->setCurrentRoute($route);
                    $this->applyHook('upfor.before.dispatch');
                    $dispatched = $route->dispatch($this);
                    $this->applyHook('upfor.after.dispatch');
                    if ($dispatched) {
                        $this->router->setCurrentRoute($route);
                        break;
                    }
                } catch (Exception $e) {
                    continue;
                }
            }

            if (!$dispatched) {
                echo 'Page Not Found';
                $this->response->status(404);
            }
        } catch (Exception $e) {
            throw $e;
        }

        $this->response->body(ob_get_clean());
        $this->response->send();
        $this->applyHook('upfor.after');

        $this->error->unregister();
    }

    /**
     * Clean current output buffer
     */
    protected function cleanBuffer() {
        if (ob_get_level() !== 0) {
            ob_clean();
        }
    }

    /**
     * Add route with multiple methods
     *
     * @param  array  $methods  Numeric array of HTTP method names
     * @param  string $pattern  The route URI pattern
     * @param  callable|string  $callback The route callback routine
     */
    public function map(array $methods, $pattern, $callback) {
        if ($callback instanceof Closure) {
            $callback = $callback->bindTo($this);
        }

        $route = $this->router->map($methods, $pattern, $callback);

        if (isset($this->setting['routes.case_sensitive'])) {
            $route->setCaseSensitive((bool) $this->setting['routes.case_sensitive']);
        }
        return $route;
    }

    /**
     * Add GET route
     *
     * @param  string $pattern  The route URI pattern
     * @param  callable|string  $callback The route callback routine
     *
     * @return \Upfor\Http\Route
     */
    public function get($pattern, $callback) {
        return $this->map(['GET'], $pattern, $callback);
    }

    /**
     * Add POST route
     *
     * @param  string $pattern  The route URI pattern
     * @param  callable|string  $callback The route callback routine
     *
     * @return \Upfor\Http\Route
     */
    public function post($pattern, $callback) {
        return $this->map(['POST'], $pattern, $callback);
    }

    /**
     * Add PUT route
     *
     * @param  string $pattern  The route URI pattern
     * @param  callable|string  $callback The route callback routine
     *
     * @return \Upfor\Http\Route
     */
    public function put($pattern, $callback) {
        return $this->map(['PUT'], $pattern, $callback);
    }

    /**
     * Add PATCH route
     *
     * @param  string $pattern  The route URI pattern
     * @param  callable|string  $callback The route callback routine
     *
     * @return \Upfor\Http\Route
     */
    public function patch($pattern, $callback) {
        return $this->map(['PATCH'], $pattern, $callback);
    }

    /**
     * Add DELETE route
     *
     * @param  string $pattern  The route URI pattern
     * @param  callable|string  $callback The route callback routine
     *
     * @return \Upfor\Http\Route
     */
    public function delete($pattern, $callback) {
        return $this->map(['DELETE'], $pattern, $callback);
    }

    /**
     * Add OPTIONS route
     *
     * @param  string $pattern  The route URI pattern
     * @param  callable|string  $callback The route callback routine
     *
     * @return \Upfor\Http\Route
     */
    public function options($pattern, $callback) {
        return $this->map(['OPTIONS'], $pattern, $callback);
    }

    /**
     * Add route for any HTTP method
     *
     * @param  string $pattern  The route URI pattern
     * @param  callable|string  $callback The route callback routine
     *
     * @return \Upfor\Http\Route
     */
    public function any($pattern, $callback) {
        return $this->map($this->router->getSupportMethods(), $pattern, $callback);
    }

    /**
     * Property Overloading
     */
    public function __get($name) {
        return $this->container[$name];
    }

    public function __set($name, $value) {
        return $this->singleton($name, $value);
    }

    public function __isset($name) {
        return isset($this->container[$name]);
    }

    public function __unset($name) {
        unset($this->container[$name]);
    }

    /**
     * Gets a single instance of a class
     * 
     * @param  string  $key   The value or object name
     * @param  Closure        The closure that defines the object
     * @return mixed
     */
    public function singleton($key, $value = null) {
        if (is_null($value) && $this->container->has($key)) {
            return $this->container->get($key);
        }

        return $this->container->singleton($key, $value);
    }

}
