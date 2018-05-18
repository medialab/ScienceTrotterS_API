<?php
$p = dirname(__FILE__).'/../../app/Http/Controllers/PublicControllers/ParcoursController.php';
require_once($p);

use App\Http\Controllers;



var_dump($p);
var_dump(file_exists($p));

var_dump(class_exists("ParcoursController"));
exit;

$router->get('/list', 'ParcoursController@list');
$router->get('/{id:[a-z0-9-]+}', 'CitiesController@get');