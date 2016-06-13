<?php

use Upfor\App;

if (!function_exists('app')) {

    /**
     * Get the specified app instance
     *
     * @param  string $name
     * @return \Upfor\App
     */
    function app($name = null) {
        static $currentApp = 'default';

        if ($name) {
            $currentApp = $name;
        }

        return App::getInstance($currentApp);
    }

}

if (!function_exists('single')) {

    /**
     * Gets a single instance of a class
     * 
     * @param  string  $key   The value or object name
     * @param  Closure        The closure that defines the object
     * @return mixed
     */
    function single($key, $value = null) {
        return app()->singleton($key, $value);
    }

}

if (!function_exists('log')) {

    /**
     * Log a debug message to the logs.
     *
     * @param  mixed $level
     * @param  mixed $message
     * @param  array $context
     * @return \Upfor\Exception\Logger|null
     */
    function log($level, $message, $context = []) {
        if (is_null($message)) {
            return single('logger');
        }

        return single('logger')->log($level, $message, $context);
    }

}