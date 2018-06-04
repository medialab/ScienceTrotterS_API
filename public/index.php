<?php

require_once(dirname(realpath('.')).'/config.php');

ini_set('xdebug.var_display_max_data', -1);
ini_set('xdebug.var_display_max_depth', -1);
ini_set('xdebug.var_display_max_children', -1);

define('ADMIN_URL', 'https://admin-sts.actu.com/');
define('UPLOAD_PATH', realpath('.').'/ressources/upload/');

if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    $_SERVER['REQUEST_METHOD'] = 'POST';
    /*header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Max-Age: 604800');
    //if you need special headers
    header('Access-Control-Allow-Headers: Authorization');*/
//    exit;
}

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| First we need to get an application instance. This creates an instance
| of the application / container and bootstraps the application so it
| is ready to receive HTTP / Console requests from the environment.
|
*/

$app = require __DIR__.'/../bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request
| through the kernel, and send the associated response back to
| the client's browser allowing them to enjoy the creative
| and wonderful application we have prepared for them.
|
*/

$app->run($app->make('request'));