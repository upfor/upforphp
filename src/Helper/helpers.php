<?php

use Upfor\App;

if (!function_exists('app')) {
    /**
     * Get the specified app instance
     *
     * @param  string $name
     * @return \Upfor\App
     */
    function app($name = 'default') {
        return App::getInstance($name);
    }

}