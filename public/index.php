<?php

define("DS", DIRECTORY_SEPARATOR);
define("ROOT", dirname(realpath(__DIR__)));

// Use composer
require ROOT . '/vendor/autoload.php';

// Or custom autoloader
//require ROOT . '/src/Helper/helpers.php';
//require ROOT . '/src/Autoloader.php';
//$autoloader = new \Upfor\Autoloader();
//$autoloader->addNamespace('Upfor\\', ROOT . '/src/');
//$autoloader->register();

$app = new \Upfor\App();

require ROOT . '/config/route.php';

$app->hook('upfor.after', function () use ($app) {
    echo '<hr>upfor.after hook<hr>';
});
$app->hook('custom.hook', function () use ($app) {
    echo '<hr>Test: custom.hook<hr>';
});

//Multi Application Mode
$app->setName('upfor');
$app = \Upfor\App::getInstance('upfor');

//Apply a hook
$app->applyHook('custom.hook');

$app->run();

