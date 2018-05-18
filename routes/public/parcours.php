<?php
$p = dirname(__FILE__).'/../../app/Http/Controllers/PublicControllers/TestController.php';
require_once($p);

use App\Http\Controllers;



var_dump($p);
var_dump(file_exists($p));

var_dump(class_exists("Test"));
exit;

$router->get('/list', 'ParcoursController@list');
$router->get('/{id:[a-z0-9-]+}', 'CitiesController@get');