# [Upfor Framework](http://framework.upfor.club)
Upfor is a simple, extensible framework for PHP. Upfor can help you quickly build simple yet powerful web applications.

## Installation
### 1. Download the files
If you’re using [Composer](https://getcomposer.org), you can run the following command:
```
composer require upfor/upforphp
```
OR you can [download](https://github.com/upfor/upforphp/archive/master.zip) them directly and extract them to your web directory.

### 2. Configure webserver
#### PHP built-in server
```
php -S 0.0.0.0:8080 -t ./public
```

#### Apache configuration
Make sure your Apache virtual host is configured with the `AllowOverride` option. Ensure your `.htaccess` file and contain this code:
```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]
```

#### Nginx configuration
For Nginx, add the following to your server declaration:
```
location / {
    try_files $uri $uri/ /index.php$is_args$args;
}
```

### 3. Create index.php file
First, include the autoloader and register a namespace.
```
define("ROOT", dirname(realpath(__DIR__)));
require ROOT . '/src/Autoloader.php';
$autoloader = new \Upfor\Autoloader();
$autoloader->addNamespace('Upfor\\', ROOT . '/src/');
$autoloader->register();
```

If you’re using Composer, run the autoloader instead.
```
require 'vendor/autoload.php';
```

Create and configure app.
```
$app = new \Upfor\App();
```

Define the application routes.
```
$route = $app->get('/', function () {
    echo 'Hello World!';
});
```

Finaly, run the application.
```
$app->run();
```

> If you are not using the Composer, you must manually require the helpers:
> `require ROOT . '/src/Helper/helpers.php';`


## Requirements
Upfor requires PHP 5.4.0 or greater.

## License
Upfor Framework is released under the MIT license.

