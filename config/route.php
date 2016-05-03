<?php

//Route Config Data

$route = $app->get('/', function () {
    echo 'Hello World!';
});
$route->setMiddleware(function () {
    echo 'This is a route middleware!';
});

$app->get('/blog/@id:[\d]+', function ($id) use ($app) {
    die($id);
});

$app->get('/admin(/@module(/@controller(/@action)))(/@id)', function ($module, $controller, $action) use ($app) {
    echo 'App: ' . $module . '<br>';
    echo 'Controller: ' . $controller . '<br>';
    echo 'Action: ' . $action . '<br>';
});
