<?php
use App\Http\Controllers;

$p = dirname(__FILE__).'/../../app/Http/Controllers/PublicControllers/ParcoursController.php';
require_once($p);


var_dump($p);
var_dump(file_exists($p));
var_dump(class_exists("Test"));
var_dump(class_exists("ParcoursController"));
exit;

$router->get('/list', 'ParcoursController@list');
$router->get('/{id:[a-z0-9-]+}', 'CitiesController@get');